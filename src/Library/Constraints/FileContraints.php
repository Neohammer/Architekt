<?php

namespace Architekt\Library\Constraints;

use Architekt\DB\Interfaces\DBEntityInterface;
use Architekt\Form\BaseConstraints;
use Architekt\Form\Validation;
use Architekt\Library\File;
use Architekt\Library\UploadFile;
use Architekt\Response\FormResponse;

class FileContraints extends BaseConstraints
{
    public static function tryUpload(
        string            $origin,
        DBEntityInterface $originEntity,
        string            $title,
        ?string           $description = null,
        string            $privacy = 'private',
        string            $inputTag = '%s',
        ?File             $file = null,
        ?array            $posted = null,
        ?string           $url = null,
        ?bool             $required = false,
    ): FormResponse
    {
        $validation = new Validation();
        $uploadResponse = null;

        if ($posted && self::isUploadValid($posted)) {
            $uploadResponse = self::tryUploadFromForm(
                $file ?? new File(),
                $posted,
                $inputTag
            );
        } elseif ($url) {
            $uploadResponse = self::tryUploadFromUrl(
                $file ?? new File(),
                $url,
                $inputTag
            );
        }

        if ($uploadResponse && $uploadResponse->isSuccess()) {
            /** @var File $file */
            $file = $uploadResponse->getArg('file');

            $title = trim($title ?? '');
            $titleTag = sprintf($inputTag, 'title');
            if (self::isEmptyString($title)) {
                $validation->addError($titleTag, 'Titre du fichier obligatoire');
            } else {
                $validation->addSuccess($titleTag, 'Titre du fichier valide');
                $file->_set('title', $title);
            }

            $description = trim($description ?? '');
            $descriptionTag = sprintf($inputTag, 'description');
            $file->_set('description');
            if (!self::isEmptyString($description)) {
                $file->_set('description', $description);
                $validation->addSuccess($descriptionTag, 'Description valide');
            }

            if ($validation->isSuccess()) {
                $file
                    ->_set([
                        'privacy' => $privacy,
                        'origin' => $origin,
                        'origin_id' => $originEntity
                    ])
                    ->_save();
            }
        } elseif ($required) {
            if($uploadResponse) {
                $validation->addResponse($uploadResponse);
            }
        }

        return $validation->response(
            'Fichier ajouté',
            'Impossible d\'ajouter le fichier',
            ['file' => $file]
        );
    }

    private static function tryUploadFromForm(
        File   $file,
        array  $postedFile,
        string $inputTag = '%s',
        string $inputName = 'file',
    ): FormResponse
    {
        $validation = new Validation();
        $fileTag = sprintf($inputTag, $inputName);

        $uploadFile = new UploadFile($postedFile);
        if ($uploadFile->requestUpload()) {
            if ($uploadFile->hasBeenUploaded()) {
                if ($file = File::upload($uploadFile, $file)) {
                    $validation->addSuccess($fileTag, 'Fichier téléversé');
                } else {
                    $validation->addError($fileTag, 'Erreur lors de la création du fichier');
                }
            } else {
                $validation->addError($fileTag, 'Erreur lors du téléversement du fichier');
            }
        } else {
            $validation->addError($fileTag, 'Aucun fichier à télécharger');
        }

        return $validation->response(
            'Fichier ajouté',
            'Imposible d\'ajouter le fichier',
            ['file' => $file]
        );
    }

    private static function tryUploadFromUrl(
        File   $file,
        string $url,
        string $inputTag = '%s',
        string $inputName = 'url',
    ): FormResponse
    {
        $validation = new Validation();
        $urlTag = sprintf($inputTag, $inputName);

        if (self::isEmptyString($url)) {
            $validation->addError($urlTag, 'Url invalide');
        } elseif (!str_starts_with($url, 'http')) {
            $validation->addError($urlTag, 'Url invalide');
        } else {
            $parts = explode('.', $url);

            if (!$parts) {
                $validation->addError($urlTag, 'Url non reconnue');
            } else {
                $filename = $parts[sizeof($parts) - 2];

                if ($file = File::createFromUrl(
                    $url,
                    $filename,
                    $file
                )) {
                    $validation->addSuccess($urlTag, 'Fichier créé à partir de l\'url');
                } else {
                    $validation->addError($urlTag, 'Impossible de créer le fichier depuis l\'url');
                }
            }
        }

        return $validation->response(
            'Fichier ajouté',
            'Impossible d\'ajouter le fichier',
            ['file' => $file]
        );
    }

    private static function isUploadValid(
        array $postedFile,
    ): bool
    {
        return (new UploadFile($postedFile))->requestUpload();
    }
}