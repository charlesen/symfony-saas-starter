<?php
// src/Service/MediaManager.php
namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\String\Slugger\SluggerInterface;

class MediaManager
{
    private $targetDirectory;
    private $slugger;
    private $filesystem;

    public function __construct(
        $targetDirectory,
        SluggerInterface $slugger,
        private ImageOptimizer $imageOptimizer,
        private Security $security,
        private LoggerInterface $logger
    ) {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
        $this->filesystem  = new Filesystem();
    }

    public function upload(UploadedFile $file, string $mediaType = 'post', $params = [])
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = strtolower($this->slugger->slug($originalFilename));
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
        $targetDirectory = $this->getTargetDirectory($mediaType);

        // On récupère uniquement le chemin après dossier public
        $fileDirectory = explode('public/', $targetDirectory)[1];

        try {
            $file->move($targetDirectory, $fileName);
            $this->imageOptimizer->resize("$targetDirectory/$fileName", $params);
        } catch (FileException $exception) {
            // ... handle exception if something happens during file upload
            /**
             * @var $user \App\Entity\User
             */
            $user = $this->security->getUser();
            $this->logger->error(
                'Failed to upload file ' . $file->getClientOriginalName() . ': ' . $exception->getMessage(),
                [
                    'user_email' => $user->getEmail(),
                    'error_message' => $exception->getMessage()
                ]
            );
        }

        return "$fileDirectory/$fileName";
    }

    /**
     * Suppression d'un fichier
     *
     * @param string $fileName
     * @param string $mediaType
     * @return void
     */
    public function deleteFile(string $fileName, string $mediaType = 'post')
    {
        try {
            $this->filesystem->remove($this->getTargetDirectory($mediaType) . "/$fileName");
        } catch (FileException $e) {
            // ... handle exception if something happens during file removal
        }
    }

    public function getTargetDirectory(string $mediaType = null)
    {
        return $mediaType ? "$this->targetDirectory/$mediaType" : $this->targetDirectory;
    }

    public function downloadFileByUrl($fileUrl, $mediaType = null)
    {
        try {
            $filename = basename($fileUrl);
            $targetDirectory = $this->getTargetDirectory($mediaType);

            // Get current directory
            if (!is_dir($targetDirectory)) {
                mkdir($targetDirectory, 0777, true);
            }
            $fileFullPath = $targetDirectory . '/' . $filename;
            $fileDownloaded = @file_put_contents($fileFullPath, file_get_contents($fileUrl));
            if ($fileDownloaded) {
                $this->imageOptimizer->resize($fileFullPath, ['maxWidth' => 800, 'maxHeight' => 600]);
                return explode('public/', $fileFullPath)[1];
            }
        } catch (FileException $exception) {
            // ... handle exception if something happens during file download
            /**
             * @var $user \App\Entity\User
             */
            $user = $this->security->getUser();
            $this->logger->error(
                'Erreur lors du téléchargement du fichier',
                [
                    'user_email' => $user->getEmail(),
                    'error_message' => $exception->getMessage(),
                    'fileUrl' => $fileUrl,
                    'fileFullPath' => $fileFullPath
                ]
            );
        }

        return false;
    }

    /**
     * Download image file from text/html content
     *
     * @param string $content
     * @return string
     */
    public function downloadImageFromContent($content, $mediaType = 'post')
    {
        // Extract image URL from content
        $fileDownloaded = null;
        $imageUrl = null;
        preg_match('/<img.*?src=[\'"](.*?)[\'"].*?>/i', $content, $matches);
        if (isset($matches[1])) {
            // On extrait la première image que l'on trouve dans le contenu
            $imageUrl = explode('?', $matches[1])[0];
            if (!empty($imageUrl)) {
                $fileDownloaded = $this->downloadFileByUrl($imageUrl, $mediaType);
            }
        }

        // On retourne l'image téléchargée ou le lien direct (distant) vers l'image
        return $fileDownloaded ?? $imageUrl;
    }
}
