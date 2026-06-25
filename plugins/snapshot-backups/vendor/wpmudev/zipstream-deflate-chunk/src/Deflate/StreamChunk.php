<?php

declare(strict_types=1);

namespace Wpmudev\ZipstreamDeflateChunk\Deflate;

use RuntimeException;
use ZipStream\DeflateStream;

class StreamChunk extends DeflateStream
{
    /**
     * The offset where the reading starts on the original stream
     *
     * @var int
     */
    protected $chunkOffset = 0;

    /**
     * Maximum length of data read
     *
     * @var int|null
     */
    protected $chunkSize = null;

    /**
     * @param resource $stream - contents of file as a stream resource
     * @param int $chunkOffset - the offset where the reading starts on the original stream
     * @param int $chunkSize - maximum length of data read
     */
    public function __construct($stream, $chunkOffset = 0, $chunkSize = null)
    {
        parent::__construct($stream);
        $this->chunkOffset = $chunkOffset;
        $this->chunkSize = $chunkSize;
        $this->rewind();
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @see seek()
     * @link http://www.php.net/manual/en/function.fseek.php
     * @throws \RuntimeException on failure.
     */
    public function rewind(): void
    {
        // deflate filter needs to be removed before rewind
        if ($this->filter) {
            $this->removeDeflateFilter();
            $this->seek(0);
            $this->addDeflateFilter($this->options);
        } else {
            $this->seek(0);
        }
    }

    /**
     * Seek to a position in the stream.
     *
     * @link http://www.php.net/manual/en/function.fseek.php
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated
     *     based on the seek offset. Valid values are identical to the built-in
     *     PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *     offset bytes SEEK_CUR: Set position to current location plus offset
     *     SEEK_END: Set position to end-of-stream plus offset.
     * @throws \RuntimeException on failure.
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if (!$this->isSeekable()) {
            throw new RuntimeException;
        }
        if (fseek($this->stream, $this->chunkOffset + $offset, $whence) !== 0) {
            throw new RuntimeException;
        }
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize(): ?int
    {
        $stats = fstat($this->stream);
        if (!isset($stats['size'])) {
            return null;
        }
        $size = $stats['size'] - $this->chunkOffset;
        if (!is_null($this->chunkSize)) {
            $size = min($size, $this->chunkSize);
        }
        return $size;
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int Position of the file pointer
     * @throws \RuntimeException on error.
     */
    public function tell(): int
    {
        $position = ftell($this->stream);
        if ($position === false) {
            throw new RuntimeException;
        }
        return $position - $this->chunkOffset;
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof(): bool
    {
        $result = feof($this->stream);
        if ($result || is_null($this->chunkSize)) {
            return $result;
        }
        return $this->tell() >= $this->chunkSize;
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     * @throws \RuntimeException if unable to read or an error occurs while
     *     reading.
     */
    public function getContents(): string
    {
        if (!$this->isReadable()) {
            throw new RuntimeException();
        }

        $result = stream_get_contents($this->stream, $this->chunkSize);

        if ($result === false) {
            throw new RuntimeException();
        }
        return $result;
    }
}