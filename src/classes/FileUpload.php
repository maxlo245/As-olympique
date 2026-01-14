<?php

namespace AsOlympique;

/**
 * Secure File Upload Handler
 *
 * Provides secure file upload handling with validation, sanitization,
 * and protection against common upload vulnerabilities.
 *
 * @package AsOlympique
 */
class FileUpload
{
    /**
     * Default allowed extensions
     */
    private const DEFAULT_ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];

    /**
     * Default allowed MIME types
     */
    private const DEFAULT_ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    /**
     * Default maximum file size (2MB)
     */
    private const DEFAULT_MAX_SIZE = 2097152;

    /**
     * Allowed file extensions
     *
     * @var array
     */
    private array $allowedExtensions;

    /**
     * Allowed MIME types
     *
     * @var array
     */
    private array $allowedMimes;

    /**
     * Maximum file size in bytes
     *
     * @var int
     */
    private int $maxSize;

    /**
     * Upload directory
     *
     * @var string
     */
    private string $uploadDir;

    /**
     * Last error message
     *
     * @var string
     */
    private string $lastError = '';

    /**
     * Constructor
     *
     * @param string $uploadDir Upload directory path
     * @param array|null $allowedExtensions Allowed file extensions
     * @param array|null $allowedMimes Allowed MIME types
     * @param int|null $maxSize Maximum file size in bytes
     */
    public function __construct(
        string $uploadDir,
        ?array $allowedExtensions = null,
        ?array $allowedMimes = null,
        ?int $maxSize = null
    ) {
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
        $this->allowedExtensions = $allowedExtensions ?? self::DEFAULT_ALLOWED_EXTENSIONS;
        $this->allowedMimes = $allowedMimes ?? self::DEFAULT_ALLOWED_MIMES;
        $this->maxSize = $maxSize ?? self::DEFAULT_MAX_SIZE;

        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Validate uploaded file
     *
     * @param array $file File array from $_FILES
     * @return bool True if valid, false otherwise
     */
    public function validate(array $file): bool
    {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->lastError = $this->getUploadErrorMessage($file['error']);
            return false;
        }

        // Check file size
        if ($file['size'] > $this->maxSize) {
            $this->lastError = sprintf(
                'File too large. Maximum size: %s',
                $this->formatBytes($this->maxSize)
            );
            return false;
        }

        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions, true)) {
            $this->lastError = sprintf(
                'Invalid file extension. Allowed: %s',
                implode(', ', $this->allowedExtensions)
            );
            return false;
        }

        // Check MIME type (real MIME, not from HTTP header)
        if (!$this->validateMimeType($file['tmp_name'])) {
            return false;
        }

        // Check for magic bytes (file signature)
        if (!$this->validateFileSignature($file['tmp_name'], $extension)) {
            $this->lastError = 'File signature does not match extension';
            return false;
        }

        return true;
    }

    /**
     * Validate MIME type using fileinfo
     *
     * @param string $filepath Path to file
     * @return bool True if valid
     */
    private function validateMimeType(string $filepath): bool
    {
        if (!file_exists($filepath)) {
            $this->lastError = 'File does not exist';
            return false;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($filepath);

        if (!in_array($mimeType, $this->allowedMimes, true)) {
            $this->lastError = sprintf('Invalid MIME type: %s', $mimeType);
            return false;
        }

        return true;
    }

    /**
     * Validate file signature (magic bytes)
     *
     * Checks the first bytes of the file to verify it matches the expected type.
     *
     * @param string $filepath Path to file
     * @param string $extension Expected extension
     * @return bool True if valid
     */
    private function validateFileSignature(string $filepath, string $extension): bool
    {
        $handle = fopen($filepath, 'rb');
        if (!$handle) {
            return false;
        }

        $bytes = fread($handle, 8);
        fclose($handle);

        // Check magic bytes for common formats
        $signatures = [
            'jpg' => ["\xFF\xD8\xFF"],
            'jpeg' => ["\xFF\xD8\xFF"],
            'png' => ["\x89\x50\x4E\x47"],
            'gif' => ["\x47\x49\x46\x38"],
            'pdf' => ["\x25\x50\x44\x46"],
        ];

        if (!isset($signatures[$extension])) {
            // Extension not in signature list, skip validation
            return true;
        }

        foreach ($signatures[$extension] as $signature) {
            if (strpos($bytes, $signature) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Upload file with secure filename
     *
     * @param array $file File array from $_FILES
     * @param string|null $customName Optional custom filename (without extension)
     * @return array|false Array with file info on success, false on failure
     */
    public function upload(array $file, ?string $customName = null)
    {
        if (!$this->validate($file)) {
            return false;
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Generate secure filename
        if ($customName !== null) {
            $filename = Validator::sanitizeFilename($customName) . '.' . $extension;
        } else {
            $filename = $this->generateSecureFilename($extension);
        }

        // Ensure filename is unique
        $destination = $this->uploadDir . $filename;
        $counter = 1;
        while (file_exists($destination)) {
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $filename = $name . '_' . $counter . '.' . $extension;
            $destination = $this->uploadDir . $filename;
            $counter++;
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->lastError = 'Failed to move uploaded file';
            return false;
        }

        // Set proper permissions
        chmod($destination, 0644);

        return [
            'original_name' => Validator::sanitizeFilename($file['name']),
            'stored_name' => $filename,
            'size' => $file['size'],
            'mime_type' => mime_content_type($destination),
            'path' => $destination,
        ];
    }

    /**
     * Generate secure random filename
     *
     * @param string $extension File extension
     * @return string Generated filename
     */
    private function generateSecureFilename(string $extension): string
    {
        return bin2hex(random_bytes(16)) . '.' . $extension;
    }

    /**
     * Get last error message
     *
     * @return string
     */
    public function getLastError(): string
    {
        return $this->lastError;
    }

    /**
     * Get upload error message
     *
     * @param int $errorCode PHP upload error code
     * @return string Error message
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension',
        ];

        return $errors[$errorCode] ?? 'Unknown upload error';
    }

    /**
     * Format bytes to human-readable size
     *
     * @param int $bytes Size in bytes
     * @param int $precision Decimal precision
     * @return string Formatted size
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
