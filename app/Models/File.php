<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'research_paper_id',
        'original_filename',
        'stored_filename',
        'file_path',
        'file_type',
        'file_size',
        'mime_type',
        'description',
        'license_type',
        'collection_methodology',
        'citation_guidelines',
        'version_number',
        'parent_file_id',
        'is_current_version',
        'folder_path',
        'visibility',
        'view_only',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'version_number' => 'integer',
            'is_current_version' => 'boolean',
            'view_only' => 'boolean',
            'download_count' => 'integer',
        ];
    }

    // Relationships

    public function researchPaper(): BelongsTo
    {
        return $this->belongsTo(ResearchPaper::class);
    }

    public function parentFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'parent_file_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(File::class, 'parent_file_id')->orderBy('version_number', 'desc');
    }

    public function downloadLogs(): HasMany
    {
        return $this->hasMany(DownloadLog::class);
    }

    // Accessors

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getHumanReadableSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getExtensionAttribute(): string
    {
        return pathinfo($this->original_filename, PATHINFO_EXTENSION);
    }

    public function getIconClassAttribute(): string
    {
        return match ($this->extension) {
            'pdf' => 'file-pdf',
            'doc', 'docx' => 'file-word',
            'xls', 'xlsx', 'csv' => 'file-excel',
            'zip', 'rar', '7z' => 'file-archive',
            'jpg', 'jpeg', 'png', 'gif', 'svg' => 'file-image',
            'mp4', 'avi', 'mov' => 'file-video',
            'mp3', 'wav' => 'file-audio',
            'txt' => 'file-text',
            'json', 'xml' => 'file-code',
            default => 'file',
        };
    }

    // Scopes

    public function scopeCurrentVersions($query)
    {
        return $query->where('is_current_version', true);
    }

    public function scopePublicFiles($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopeInFolder($query, string $path)
    {
        return $query->where('folder_path', $path);
    }

    // Helper Methods

    public function createNewVersion(array $attributes): self
    {
        // Mark current version as not current
        $this->update(['is_current_version' => false]);

        // Create new version
        return self::create(array_merge($attributes, [
            'research_paper_id' => $this->research_paper_id,
            'parent_file_id' => $this->parent_file_id ?? $this->id,
            'version_number' => $this->version_number + 1,
            'is_current_version' => true,
        ]));
    }

    public function isImage(): bool
    {
        return in_array($this->extension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']);
    }

    public function isPdf(): bool
    {
        return $this->extension === 'pdf';
    }

    public function isCode(): bool
    {
        return in_array($this->extension, [
            'php', 'js', 'py', 'java', 'cpp', 'c', 'h', 'cs',
            'rb', 'go', 'rs', 'swift', 'kt', 'ts', 'jsx', 'tsx',
            'html', 'css', 'scss', 'sass', 'json', 'xml', 'yml', 'yaml',
        ]);
    }

    public function isArchive(): bool
    {
        return in_array($this->extension, ['zip', 'rar', '7z', 'tar', 'gz']);
    }

    public function incrementDownloads(): void
    {
        $this->increment('download_count');
    }

    public function canDownload(): bool
    {
        return !$this->view_only && $this->visibility !== 'private';
    }

    public function getAllVersions(): \Illuminate\Support\Collection
    {
        if ($this->parent_file_id) {
            return $this->parentFile->versions;
        }

        return $this->versions;
    }
}
