<?php

namespace Mayflower\Oci8TestBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Mayflower\Oci8TestBundle\Entity\Asset;
use Mayflower\Oci8TestBundle\Entity\AssetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class DefaultController
 *
 */
class DefaultController extends Controller
{

    /**
     * List all stored Images.
     *
     * @return Response
     */
    public function indexAction()
    {
        /** @var AssetRepository $assetRepo */
        $em            = $this->getDoctrine()->getManager();
        $assetRepo     = $em->getRepository(Asset::NAME);
        $assets        = $assetRepo->findAll();
        $assetToRender = [];
        $totalSize     = 0;
        /** @var Asset $asset */
        foreach ($assets as $id => $asset) {
            $stats = fstat($asset->getContentStream());
            $size  = $asset->getFileSize();
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
            'assets'    => $assetToRender,
            'totalSize' => $totalSize,
            ]
        );
    }

    /**
     * Return One image content with set the Content-Type
     *
     * @param string $id Image Id in Database.
     *
     * @return StreamedResponse
     */
    public function imageAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var AssetRepository $assetRepo */
        $assetRepo = $em->getRepository(Asset::NAME);
        /** @var Asset $asset */
        $asset = $assetRepo->find($id);

        $headers = [
            'Content-Length' => $asset->getFileSize(),
            'Content-Type'   => $asset->getMimeType(),
        ];

        return new StreamedResponse(
            function () use ($asset) {
                echo $asset->getContent();
                ob_flush();
                flush();
            },
            200,
            $headers
        );
    }

    /**
     * @return Response
     */
    public function formAction()
    {
        $formBuilderImage = $this->createFormBuilder();
        $formBuilderImage->add("image", 'file', ['required' => true, 'label' => "File to Upload: "]);
        $formBuilderImage->add("Submit", 'submit');
        $notice      = '';
        $trace       = '';
        $noticeColor = 'green';

        $form    = $formBuilderImage->getForm();
        $request = $this->getRequestStack()->getMasterRequest();

        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            if ($form->isValid() && !$form->getData()['image']->getError()) {
                try {
                    /** @var EntityManagerInterface $em */
                    $em = $this->getDoctrine()->getManager();
                    /** @var AssetRepository $assetRepo */
                    $assetRepo = $em->getRepository(Asset::NAME);
                    $maxId     = $assetRepo->getMaxId();

                    /** @var UploadedFile $content */
                    $content      = $form->getData()['image'];
                    $size         = $content->getSize();
                    $mimeType     = $content->getClientMimeType();
                    $origName     = $content->getClientOriginalName();
                    $uploadedFile = $content->getRealPath();

                    $newAsset = new Asset();
                    $newAsset->setId($maxId + 1);
                    $newAsset->setFileName($origName);
                    $newAsset->setFileSize($size);
                    $newAsset->setMimeType($mimeType);
                    $newAsset->setContent(file_get_contents($uploadedFile));

                    $em->persist($newAsset);
                    $em->flush();

                    $notice = "{$origName} is Uploaded!";
                } catch (\Exception $e) {
                    $notice      = 'Failure to Upload File!';
                    $trace       = $e->getTraceAsString();
                    $noticeColor = 'red';
                }
            } else {
                /** @var UploadedFile $content */
                $content     = $form->getData()['image'];
                $notice      = $content->geterrorMessage();
                $noticeColor = 'red';
            }
        }


        return $this->render(
            'MayflowerOci8TestBundle:Default:upload.html.twig',
            ['notice' => $notice, 'noticeColor' => $noticeColor, 'trace' => $trace, 'form' => $form->createView()]
        );

    }

    /**
     * @return RequestStack
     */
    protected function getRequestStack()
    {
        return $this->container->get('request_stack');
    }

    /**
     * Method to calculate the human readable formate of a byte size.
     *
     * @param int    $size     The size in byte to convert in human readable format
     * @param string $unit     Set to a unit to force calculating for this unit.
     * @param int    $decimals How many decimals will be displayed.
     *
     * @return string
     */
    private function getHumanReadableSize($size, $unit = null, $decimals = 2)
    {
        $byteUnits = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        if (!is_null($unit) && !in_array($unit, $byteUnits)) {
            $unit = null;
        }
        $extent = 1;
        $rank   = 'B';

        foreach ($byteUnits as $rank) {
            if ((is_null($unit) && ($size < $extent <<= 10)) || ($rank == $unit)) {
                break;
            }
        }

        return number_format($size / ($extent >> 10), $decimals) . $rank;
    }
}
