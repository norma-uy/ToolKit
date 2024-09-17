<?php

namespace App\Controller;

use Knp\Bundle\SnappyBundle\Snappy\Response\JpegResponse;
use Knp\Snappy\Image;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class GeneratorController extends AbstractController
{
    private Image $knpSnappyImage;
    private array $imageDefaultOptions;
    private string $imageDefaultFormat;
    private array $validImageFormats;

    public function __construct(Image $knpSnappyImage)
    {
        $this->knpSnappyImage = $knpSnappyImage;
        $this->imageDefaultOptions = [];
        $this->imageDefaultFormat = 'jpg';
        $this->validImageFormats = ['jpg', 'png'];
    }

    /**
     * Image URL Generator.
     */
    #[Route('/image-url-generator', name: 'image_url_generator', methods: ['GET'])]
    public function imageURLGenerator(Request $request): Response
    {
        $url = $request->get('url');

        if (!$url) {
            throw new BadRequestHttpException('The URL parameter is required');
        }

        $width = intval($request->get('width', 600));
        $height = intval($request->get('height', 1200));
        $quality = intval($request->get('quality', 100));
        $format = (string) $request->get('format', $this->imageDefaultFormat);
        $format = in_array($format, $this->validImageFormats) ? $format : $this->imageDefaultFormat;

        $filename = $request->get('filename', "output.{$format}");
        $disposition = $request->get('disposition', 'inline');

        $options = [
            ...$this->imageDefaultOptions,
            ...($width ? ['width' => $width] : []),
            ...($height ? ['height' => $height] : []),
            ...($quality ? ['quality' => $quality] : []),
            ...($format ? ['format' => $format] : []),
        ];

        $this->knpSnappyImage->setOptions($options);

        return new JpegResponse(
            $this->knpSnappyImage->getOutput($url),
            $filename,
            "image/{$format}",
            $disposition,
        );
    }
}
