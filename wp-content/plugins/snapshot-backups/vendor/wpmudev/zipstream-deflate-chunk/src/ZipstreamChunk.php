<?php

namespace Wpmudev\ZipstreamDeflateChunk;

use Wpmudev\ZipstreamDeflateChunk\Deflate\StreamChunk;
use ZipStream\File;
use ZipStream\Option\Archive as ArchiveOptions;
use ZipStream\ZipStream;
use ZipStream\Option\File as FileOption;

class ZipstreamChunk extends ZipStream
{

	/**
	 * Create a new ZipStream object.
	 *
	 * Parameters:
	 *
	 * @param String $name - Name of output file (optional).
	 * @param ArchiveOptions $opt - Archive Options
	 *
	 * Large File Support:
	 *
	 * By default, the method addFileFromPath() will send send files
	 * larger than 20 megabytes along raw rather than attempting to
	 * compress them.  You can change both the maximum size and the
	 * compression behavior using the largeFile* options above, with the
	 * following caveats:
	 *
	 * * For "small" files (e.g. files smaller than largeFileSize), the
	 *   memory use can be up to twice that of the actual file.  In other
	 *   words, adding a 10 megabyte file to the archive could potentially
	 *   occupy 20 megabytes of memory.
	 *
	 * * Enabling compression on large files (e.g. files larger than
	 *   large_file_size) is extremely slow, because ZipStream has to pass
	 *   over the large file once to calculate header information, and then
	 *   again to compress and send the actual data.
	 *
	 * Examples:
	 *
	 *   // create a new zip file named 'foo.zip'
	 *   $zip = new ZipStream('foo.zip');
	 *
	 *   // create a new zip file named 'bar.zip' with a comment
	 *   $opt->setComment = 'this is a comment for the zip file.';
	 *   $zip = new ZipStream('bar.zip', $opt);
	 *
	 * Notes:
	 *
	 * In order to let this library send HTTP headers, a filename must be given
	 * _and_ the option `sendHttpHeaders` must be `true`. This behavior is to
	 * allow software to send its own headers (including the filename), and
	 * still use this library.
	 */
	public function __construct(string $name, ?ArchiveOptions $options = null)
	{
		parent::__construct($name, $options);
	}

	/**
	 * addFileFromStreamChunk
	 *
	 * Add an open stream and start from `offset` to the archive.
	 *
	 * @param String $name - path of file in archive (including directory).
	 * @param Resource $stream - contents of file as a stream resource
	 * @param FileOption $options
	 * @param int $chunkOffset - the offset where the reading starts on the original stream
	 * @param int $chunkSize - maximum length of data read
	 *
	 * File Options:
	 *  time     - Last-modified timestamp (seconds since the epoch) of
	 *             this file.  Defaults to the current time.
	 *  comment  - Comment related to this file.
	 *
	 * Examples:
	 *
	 *   // create a temporary file stream and write text to it
	 *   $fp = tmpfile();
	 *   fwrite($fp, 'The quick brown fox jumped over the lazy dog.');
	 *
	 *   // add a file named 'streamfile.txt' from the content of the stream
	 *   $x->addFileFromStreamChunk('streamfile.txt', $fp, null, 36, 8);
	 */
	public function addFileFromStreamChunk(
		string $name,
		$stream,
		FileOption $options = null,
		$chunkOffset = 0,
		$chunkSize = null
	) {
		$options = $options ?: new FileOption();
		if (!$options->getSize() && !is_null($chunkSize)) {
			$options->setSize($chunkSize);
		}
		$options->defaultTo($this->opt);

		$file = new File($this, $name, $options);
		$file->processStream(new StreamChunk($stream, $chunkOffset, $chunkSize));
	}
}