<?php


namespace Mayflower\Oci8TestBundle\Library;


class StreamWrapperOci8Lob {

    /**
     * @var OCI-Lob
     */
    var $oci_lob_obj;

    /**
     * @var Resource
     */
    var $context;

    function stream_case($case_as)
    {
        return false;
    }

    function stream_open($path, $mode, $options, &$opened_path)
    {
        $url = parse_url($path);
        $context = stream_context_get_options($this->context);
        $this->oci_lob_obj = $context[$url['scheme']][$url['host']];

        return true;
    }

    function stream_read($count)
    {
        return $this->oci_lob_obj->read($count);
    }

    function stream_write($data)
    {
        return $this->oci_lob_obj->write($data);
    }

    function stream_tell()
    {
        return $this->oci_lob_obj->tell();
    }

    function stream_eof()
    {
        return $this->oci_lob_obj->eof();
    }

    function stream_seek($offset, $whence)
    {
        return $this->oci_lob_obj->seek($offset, $whence);
    }

    function stream_metadata($path, $option, $var)
    {
        return false;
    }

    function stream_close()
    {
        $this->oci_lob_obj->free();
    }

    function stream_stat()
    {
        $size = $this->oci_lob_obj->size();
        $now = date("U");
        return [
            0 => 999,
            1 => 0,
            2 => 33060,
            3 => 1,
            4 => 0,
            5 => 0,
            6 => -1,
            7 => $size,
            8 => $now,
            9 => $now,
            10 => $now,
            11 => -1,
            12 => -1,
            'dev' => 999,
            'ino' => 0,
            'mode' => 33060,
            'nlink' => 1,
            'uid' => 0,
            'gid' => 0,
            'rdev' => -1,
            'size' => $size,
            'atime' => $now,
            'mtime' => $now,
            'ctime' => $now,
            'blksize' => -1,
            'blocks' => -1,
        ];
    }
} 