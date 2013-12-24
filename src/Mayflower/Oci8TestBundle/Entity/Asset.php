<?php

namespace Mayflower\Oci8TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Asset
 *
 * @ORM\Table(name="TEST_ASSET")
 * @ORM\Entity(repositoryClass="Mayflower\Oci8TestBundle\Entity\AssetRepository")
 */
class Asset
{
    const NAME          = __CLASS__;
    const SEQUENCE_NAME = 'MO3_FILES';

    /**
     * @var int $id
     *
     * @ORM\Column(name="ID", type="integer", nullable=false)
     * @ORM\Id
     */
    protected $id;

    /**
     * @var string $content
     *
     * @ORM\Column(name="CONTENT", type="blob", nullable=false)
     */
    protected $content;

    /**
     * @var string $filename
     *
     * @ORM\Column(name="FILENAME", type="string", length=255, nullable=false)
     */
    protected $fileName;

    /**
     * @var int $filesize
     *
     * @ORM\Column(name="FILESIZE", type="integer", nullable=false)
     */
    protected $fileSize;

    /**
     * @var string $mimeType
     *
     * @ORM\Column(name="MIME_TYPE", type="string", length=255, nullable=false)
     */
    protected $mimeType;

    /**
     * @var \Doctrine\ORM\Id\SequenceGenerator
     */
    protected $sequenceGenerator;

    /**
     * setter
     *
     * @param int $id The current file id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * setter
     *
     * @param string $content The file content
     *
     * @return void
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Setter
     *
     * @param int $fileSize The file size
     *
     * @return void
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;
    }

    /**
     * Setter
     *
     * @param string $mimeType File mime type
     *
     * @return void
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    /**
     * Setter
     *
     * @param string $fileName The file name
     *
     * @return void
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * getter
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * getter
     *
     * @return string
     */
    public function getContent()
    {
        if (is_resource($this->content)) {
            return stream_get_contents($this->content, -1, 0);
        }

        return $this->content;
    }

    /**
     * Returns the file content as stream
     *
     * @return null|resource
     */
    public function getContentStream()
    {
        if (is_resource($this->content)) {
            return $this->content;
        }

        return null;
    }

    /**
     * getter
     *
     * @return int
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * getter
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * getter
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }
}
