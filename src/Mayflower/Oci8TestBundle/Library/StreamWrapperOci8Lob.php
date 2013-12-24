<?php


namespace Mayflower\Oci8TestBundle\Library;

/**
 * Class StreamWrapperOci8Lob
 */
class StreamWrapperOci8Lob
{

    /**
     * @var \OCI_Lob
     */
    public $ociLobObj;

    /**
     * @var Resource
     */
    public $context;

    /**
     * Check if stream is cased as.
     *
     * @param int $case_as Case type.
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-cast.php
     *
     * @return bool
     */
    function stream_case($case_as)
    {
        return false;
    }

    /**
     * Stream Open method
     *
     * @param string $path         Uri path from fopen
     * @param string $mode         open mode
     * @param array  $options      options
     * @param string &$opened_path open path
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-open.php
     *
     * @return bool
     */
    function stream_open($path, $mode, $options, &$opened_path)
    {
        $url = parse_url($path);

        // can not open stream if path has wrong syntax.
        if (!array_key_exists('scheme', $url) || !array_key_exists('host', $url)) {
            return false;
        }
        $scheme  = $url['scheme'];
        $varName = $url['host'];

        $context = stream_context_get_options($this->context);

        // can not open stream if context is not set right.
        if (!array_key_exists($scheme, $context)) {
            return false;
        } elseif (!array_key_exists($varName, $context[$scheme])) {
            return false;
        }

        /** @var \OCI_Lob $ociLob */
        $ociLob = $context[$scheme][$varName];

        if (!(is_object($ociLob) && get_class($ociLob) == 'OCI-Lob')) {
            return false;
        }

        if (substr($mode, 0, 1) == 'w') {
            $ociLob->rewind();
            $ociLob->truncate();
        } elseif (substr($mode, 0, 1) == 'r') {
            $ociLob->rewind();
        } else {
            return false;
        }

        $this->ociLobObj = $ociLob;

        return true;
    }

    /**
     * Stream read method
     *
     * @param int $count Bytes to read from stream
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-read.php
     *
     * @return string
     */
    function stream_read($count)
    {
        return $this->ociLobObj->read($count);
    }

    /**
     * Stream write method
     *
     * @param string $data Data to write in stream
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-write.php
     *
     * @return int
     */
    function stream_write($data)
    {
        return $this->ociLobObj->write($data);
    }

    /**
     * Stream tell method
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-tell.php
     *
     * @return int
     */
    function stream_tell()
    {
        return $this->ociLobObj->tell();
    }

    /**
     * Stream eof method
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-eof.php
     *
     * @return bool
     */
    function stream_eof()
    {
        return $this->ociLobObj->eof();
    }

    /**
     * Stream method seek
     *
     * @param int $offset Offset to seek
     * @param int $whence Where the offset is used relative or absolute.
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-seek.php
     *
     * @return bool
     */
    function stream_seek($offset, $whence)
    {
        return $this->ociLobObj->seek($offset, $whence);
    }

    /**
     * Stream method to set metadata
     *
     * @param string $path   uri
     * @param int    $option option
     * @param mixed  $var    var
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-metadata.php
     *
     * @return bool
     */
    function stream_metadata($path, $option, $var)
    {
        return false;
    }

    /**
     * Stream method close
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-close.php
     */
    function stream_close()
    {
        $this->ociLobObj->free();
    }

    /**
     * Stream method stat
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-stat.php
     *
     * @return array
     */
    function stream_stat()
    {
        $size = $this->ociLobObj->size();
        $now  = date("U");

        return [
            0         => 999,
            1         => 0,
            2         => 33060,
            3         => 1,
            4         => 0,
            5         => 0,
            6         => -1,
            7         => $size,
            8         => $now,
            9         => $now,
            10        => $now,
            11        => -1,
            12        => -1,
            'dev'     => 999,
            'ino'     => 0,
            'mode'    => 33060,
            'nlink'   => 1,
            'uid'     => 0,
            'gid'     => 0,
            'rdev'    => -1,
            'size'    => $size,
            'atime'   => $now,
            'mtime'   => $now,
            'ctime'   => $now,
            'blksize' => -1,
            'blocks'  => -1,
        ];
    }
}
