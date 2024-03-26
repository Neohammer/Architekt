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
    public static bool $_titleAndDescriptionActive = false;

    public static function tryUpload(
        string            $origin,
        DBEntityInterface $originEntity,
        array             $posted,
        ?string           $title = null,
        ?string           $description = null,
        string            $privacy = 'private',
        string            $inputTag = '%s',
        ?File             $file = null,
        ?string           $url = null,
        ?bool             $required = false,
        ?bool             $titleRequired = false,
        ?bool             $descriptionRequired = false,
        string            $inputFileName = 'file',
    ): FormResponse
    {
        $validation = new Validation();
        $uploadResponse = null;

        if ($posted) {
            if (self::isUploadValid($posted)) {
                $uploadResponse = self::tryUploadFromForm(
                    $file ?? new File(),
                    $posted,
                    $inputTag
                );
            } elseif ($required) {
                $validation->addError($inputFileName, 'Vous devez sélectionner un fichier');
            }
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
        }
        if (!$file) {
            if ($required && $uploadResponse) {
                $validation->addResponse($uploadResponse);
            }
        } else {
            if(self::$_titleAndDescriptionActive) {
                $title = trim($title ?? '');
                if (self::isEmptyString($title)) {
                    if ($titleRequired) {
                        $validation->addError('title', 'Titre du fichier obligatoire', $inputTag);
                    }
                } else {
                    $validation->addSuccess('title', 'Titre du fichier valide', $inputTag);
                    $file->_set('title', $title);
                }

                $description = trim($description ?? '');
                $file->_set('description');
                if (self::isEmptyString($description)) {
                    if ($descriptionRequired) {
                        $validation->addError('description', 'Description obligatoire', $inputTag);
                    }
                } else {
                    $file->_set('description', $description);
                    $validation->addSuccess('description', 'Description valide', $inputTag);
                }
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
        }

        return $validation->response(
            'Fichier ajouté',
            'Impossible d\'ajouter le fichier',
            ['file' => $file]
        );
    }

    public static function tryFromString(
        string            $origin,
        DBEntityInterface $originEntity,
        string            $content,
        string            $filename,
        ?string           $title = null,
        ?string           $description = null,
        string            $privacy = 'private',
        string            $inputTag = '%s',
        ?File             $file = null,
        ?bool             $required = false,
        ?bool             $titleRequired = false,
        ?bool             $descriptionRequired = false,
    ): FormResponse
    {
        $validation = new Validation();

        $createResponse = self::tryCreateFromString(
            $filename,
            $content,
            $file,
        );

        if ($createResponse && $createResponse->isSuccess()) {
            /** @var File $file */
            $file = $createResponse->getArg('file');
        }
        if (!$file) {
            if ($required && $createResponse) {
                $validation->addResponse($createResponse);
            }
        } else {
            if(self::$_titleAndDescriptionActive) {
                $title = trim($title ?? '');
                if (self::isEmptyString($title)) {
                    if ($titleRequired) {
                        $validation->addError('title', 'Titre du fichier obligatoire', $inputTag);
                    }
                } else {
                    $validation->addSuccess('title', 'Titre du fichier valide', $inputTag);
                    $file->_set('title', $title);
                }

                $description = trim($description ?? '');
                $file->_set('description');
                if (self::isEmptyString($description)) {
                    if ($descriptionRequired) {
                        $validation->addError('description', 'Description obligatoire', $inputTag);
                    }
                } else {
                    $file->_set('description', $description);
                    $validation->addSuccess('description', 'Description valide', $inputTag);
                }
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

    private static function tryCreateFromString(
        string $filename,
        string $content,
        ?File  $file = null,
    ): FormResponse
    {
        $validation = new Validation();
        if (!$file) {
            $file = new File();
        }

        if (self::isEmptyString($content)) {
            $validation->addError('content', 'Le contenu ne peut être vide');
        } else {
            $file = File::createFromString($content, $filename, $file);

            if (!$file) {
                $validation->addError('content', 'Erreur lors de la création');
            }
        }

        return $validation->response(
            'Fichier ajouté',
            'Impossible d\'ajouter le fichier',
            ['file' => $file]
        );
    }

    public static function tryDelete(
        File $file
    ): FormResponse
    {
        $validation = new Validation();

        $file->_delete();

        return $validation->response(
            'Fichier supprimé',
            'Impossible de supprimer le fichier',
        );
    }

    private static function isUploadValid(
        array $postedFile,
    ): bool
    {
        return (new UploadFile($postedFile))->requestUpload();
    }
}