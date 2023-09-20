<?php

namespace Architekt\Library\FileCategory;

use Architekt\Form\BaseConstraints;
use Architekt\Form\Validation;
use Architekt\Library\FileCategory;
use Architekt\Response\FormResponse as Response;

class Constraints extends BaseConstraints
{
    public static function validateEditAction(
        FileCategory $fileCategory,
        ?string      $name
    ): Response
    {
        $validation = new Validation();

        $name = trim($name);
        if (self::isEmptyString($name)) {
            $validation->addError('name', 'Le nom est obligatoire');
        } elseif (!$fileCategory->isFieldValueUnique('name', $name)) {
            $validation->addError('name', 'Le nom existe déjà');
        } else {
            $validation->addSuccess('name', 'Nom valide');
            $fileCategory->_set('name', $name);
        }

        $successText = 'Catégorie de fichier ajouté';
        $failText = 'Impossible d\'ajouter la catégorie de fichier';
        if ($fileCategory->_isLoaded()) {
            $successText = 'Catégorie de fichier modifié';
            $failText = 'Impossible de modifier la catégorie de fichier';
        }

        if ($validation->isSuccess()) {
            $fileCategory->_save();
        }

        return $validation->response(
            $successText,
            $failText
        );
    }

}