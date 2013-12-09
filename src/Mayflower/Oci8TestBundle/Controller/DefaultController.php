<?php

namespace Mayflower\Oci8TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Mayflower\Oci8TestBundle\Entity\Asset;
use Mayflower\Oci8TestBundle\Entity\AssetRepository;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        /** @var AssetRepository $assetRepo */
        $em            = $this->getDoctrine()->getManager();
        $assetRepo     = $em->getRepository(Asset::NAME);
        $assets        = $assetRepo->findAll();
        $assetToRender = [];
        $totalSize = 0;
        /** @var Asset $asset */
        foreach ($assets as $id => $asset) {
            $stats = fstat($asset->getContentStream());
            $size = $asset->getFileSize();
            if ($stats['size'] != $asset->getFileSize()) {
                $size = $stats['size'];
            }
            $assetToRender[] = [
                'row'      => $id,
                'id'       => $asset->getId(),
                'filename' => $asset->getFileName(),
                'filesize' => $size,
                'mimetype' => $asset->getMimeType(),
            ];
            $totalSize += $size;
        }

        $totalSize = $this->getHumanReadableSize($totalSize);

        return $this->render(
            'MayflowerOci8TestBundle:Default:index.html.twig',
            [
                'name'      => $name,
                'assets'    => $assetToRender,
                'totalSize' => $totalSize,
            ]
        );
    }

    public function imageAction($name)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var AssetRepository $assetRepo */
        $assetRepo = $em->getRepository(Asset::NAME);
        /** @var Asset $asset */
        $asset = $assetRepo->find($name);

        $headers = [
            'Content-Length' => $asset->getFileSize(),
            'Content-Type'   => $asset->getMimeType(),
        ];

        return new StreamedResponse(function () use ($asset) {
            echo $asset->getContent();
            ob_flush();
            flush();
        }, 200, $headers);
    }

    private function getHumanReadableSize($size, $unit = null, $decemals = 2) {
        $byteUnits = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        if (!is_null($unit) && !in_array($unit, $byteUnits)) {
            $unit = null;
        }
        $extent = 1;
        foreach ($byteUnits as $rank) {
            if ((is_null($unit) && ($size < $extent <<= 10)) || ($rank == $unit)) {
                break;
            }
        }
        return number_format($size / ($extent >> 10), $decemals) . $rank;
    }
}
