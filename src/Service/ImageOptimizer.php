<?php

namespace App\Service;

use Exception;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Psr\Log\LoggerInterface;

class ImageOptimizer
{
    private const MAX_WIDTH = 800;
    private const MAX_HEIGHT = 800;

    private $imagine;

    public function __construct(private LoggerInterface $logger)
    {
        $this->imagine = new Imagine();
    }

    public function resize(string $filename, $params = []): void
    {
        try {
            list($iwidth, $iheight) = getimagesize($filename);
            $ratio = $iwidth / $iheight;
            $width = $params['maxWidth'] ?? self::MAX_WIDTH;
            $height = $params['maxHeight'] ?? self::MAX_HEIGHT;
            if ($width / $height > $ratio) {
                $width = $height * $ratio;
            } else {
                $height = $width / $ratio;
            }

            $photo = $this->imagine->open($filename);
            $photo->resize(new Box($width, $height))->save($filename);
        } catch (Exception $exception) {
            //throw $th;
            $user = $this->security->getUser();
            $this->logger->error(
                'Failed to upload file ' . $filename . ': ' . $exception->getMessage(),
                [
                    'user_email' => $user->getEmail(),
                    'error_message' => $exception->getMessage()
                ]
            );
        }
    }
}
