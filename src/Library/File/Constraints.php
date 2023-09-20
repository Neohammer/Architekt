<?php

namespace Architekt\Library\File;

use Architekt\Form\BaseConstraints;
use Architekt\Form\Exceptions\FileUploadErrorException;
use Architekt\Form\Exceptions\FileUploadFailException;
use Architekt\Form\Exceptions\FileUploadRequiredException;
use Architekt\Form\Validation;
use Architekt\Library\File;
use Architekt\Library\FileCategory;
use Architekt\Response\FormResponse as Response;
use Gmao\User;
use Gmao\Equipment;
use Gmao\Operation;
use Gmao\Subset;
use Gmao\WorkOrder;

class Constraints extends BaseConstraints
{
    public static function validateEditFromWorkOrderAction(
        User    $user,
        ?File   $file,
        ?string $title,
        ?string $status
    )
    {
        $validation = new Validation();

        $title = trim($title);
        if (self::isEmptyString($title)) {
            $validation->addError('title', 'Le libellé est obligatoire');
        } else {
            $validation->addSuccess('name', 'Libellé valide');
            $file->_set('title', $title);
        }

        if ($user->isGmaoAdmin()) {
            $file->_set('status', $status);
        }

        if ($validation->isSuccess()) {
            $file->_save();
        }

        return $validation->response(
            'Fichier modifié',
            'Impossible de modifier le fichier'
        );
    }

    public static function validateEditFromValidationAction(
        User    $user,
        ?File   $file,
        ?string $title,
        ?string $status
    )
    {
        $validation = new Validation();

        $title = trim($title);
        if (self::isEmptyString($title)) {
            $validation->addError('title', 'Le libellé est obligatoire');
        } else {
            $validation->addSuccess('name', 'Libellé valide');
            $file->_set('title', $title);
        }

        if ($user->isGmaoAdmin()) {
            $file->_set('status', $status);
        }

        if ($validation->isSuccess()) {
            $file->_save();
        }

        return $validation->response(
            'Fichier modifié',
            'Impossible de modifier le fichier'
        );
    }

    public static function validateSubAddAction(
        User    $user,
        ?string $equipmentId,
        ?string $subsetId,
        ?string $externalType,
        ?string $externalId,
        ?string $fileCategoryId,
        ?string $title,
        ?string $access,
        ?array  $uploadFile
    ): Response
    {
        $validation = new Validation();
        $file = new File();

        if ($externalType === File::TYPE_OPERATION) {
            $operation = new Operation($externalId);
            if (!$operation->_isLoaded()) {
                $validation->addError('external_id', 'Soucis avec l\'opération');
            }

            $file->_set([
                $operation->equipment(),
                'external_type' => $externalType,
                'external_id' => $operation->_primary(),
                'access' => Operation::fileDefaultAccess(),
                new FileCategory(Operation::fileDefaultCategory()),
            ]);
            if ($operation->subset()->_isLoaded()) {
                $file->_set($operation->subset());
            }
        }

        if ($externalType === File::TYPE_EQUIPMENT) {
            $equipment = new Equipment($equipmentId);
            if (!$equipment->_isLoaded()) {
                $validation->addError('equipment_id', 'Soucis avec l\'équipement');
            } else {
                $validation->addSuccess('equipment_id', 'Equipement valide');
            }

            $fileCategory = new FileCategory($fileCategoryId);
            if (!$fileCategory->_isLoaded()) {
                $validation->addError('file_category_id', 'Soucis avec la catégorie');
            } else {
                $validation->addSuccess('file_category_id', 'Catégorie valide');
            }

            $subset = new Subset($subsetId);
            if (!$subset->_isLoaded()) {
                $validation->addError('subset_id', 'Sous-ensemble invalide');
            } else {
                $validation->addSuccess('subset_id', 'Sous-ensemble valide');
            }

            if (!in_array($access, File::$allowedAccesses)) {
                $validation->addError('access', 'Accès invalide');
            } else {
                $validation->addSuccess('access', 'Accès valide');
            }

            if ($validation->isSuccess()) {
                $file->_set([
                    $equipment,
                    $subset,
                    'external_type' => $externalType,
                    'external_id' => $equipment->_primary(),
                    'access' => $access,
                    $fileCategory,
                ]);
            }
        }

        if ($externalType === File::TYPE_WORK_ORDER) {
            $workOrder = new WorkOrder($externalId);
            if (!$workOrder->_isLoaded()) {
                $validation->addError('external_id', 'Soucis avec l\'ordre de travail');
            }
            $file->_set([
                $workOrder->equipment(),
                'external_type' => $externalType,
                'external_id' => $workOrder->_primary(),
                'access' => WorkOrder::fileDefaultAccess(),
                'status' => (int)$user->isGmaoAdmin(),
                new FileCategory(WorkOrder::fileDefaultCategory()),
            ]);
            if ($workOrder->subset()->_isLoaded()) {
                $file->_set($workOrder->subset());
            }
        }

        $title = trim($title);
        if (self::isEmptyString($title)) {
            $validation->addError('title', 'Le libellé est obligatoire');
        } else {
            $validation->addSuccess('name', 'Libellé valide');
            $file->_set('title', $title);
        }

        try {
            $file = self::validateFile($file, $uploadFile, ['required' => true]);
        } catch (FileUploadErrorException) {
            $validation->addError('file', 'Impossible de récupérer le fichier');
        } catch (FileUploadFailException) {
            $validation->addError('file', 'Impossible de téléverser le fichier');
        } catch (FileUploadRequiredException) {
            $validation->addError('file', 'Vous devez préciser un fichier');
        }

        if ($validation->isSuccess()) {
            $file->_set('author_id', $user);

            $file->_save();
        }

        return $validation->response(
            'Fichier ajouté',
            'Impossible d\'ajouter le fichier'
        );
    }

    public static function validateSubEditAction(
        File    $file,
        User    $user,
        ?string $equipmentId,
        ?string $subsetId,
        ?string $fileCategoryId,
        ?string $title,
        ?string $access,
        ?array  $uploadFile
    ): Response
    {
        $validation = new Validation();

        $equipment = new Equipment($equipmentId);
        if (!$equipment->_isLoaded()) {
            $validation->addError('equipment_id', 'Soucis avec l\'équipement');
        } else {
            $validation->addSuccess('equipment_id', 'Equipement valide');
        }

        $fileCategory = new FileCategory($fileCategoryId);
        if (!$fileCategory->_isLoaded()) {
            $validation->addError('file_category_id', 'Soucis avec la catégorie');
        } else {
            $validation->addSuccess('file_category_id', 'Catégorie valide');
            $file->_set($fileCategory);
        }

        $subset = new Subset($subsetId);
        if (!$subset->_isLoaded()) {
            $validation->addError('subset_id', 'Sous-ensemble invalide');
        } else {
            $validation->addSuccess('subset_id', 'Sous-ensemble valide');
            $file->_set($subset);
        }

        if (!in_array($access, File::$allowedAccesses)) {
            $validation->addError('access', 'Accès invalide');
        } else {
            $validation->addSuccess('access', 'Accès valide');
            $file->_set('access', $access);
        }

        $title = trim($title);
        if (self::isEmptyString($title)) {
            $validation->addError('title', 'Le libellé est obligatoire');
        } else {
            $validation->addSuccess('name', 'Libellé valide');
            $file->_set('title', $title);
        }

        try {
            if( $fileUploaded = self::validateFile($file, $uploadFile) )
            {
                $file = $fileUploaded;
            }
            $validation->addSuccess('file', 'Fichier OK');
        } catch (FileUploadErrorException) {
            $validation->addError('file', 'Impossible de récupérer le fichier');
        } catch (FileUploadFailException) {
            $validation->addError('file', 'Impossible de téléverser le fichier');
        }

        if ($validation->isSuccess()) {
            $file->_set('author_id', $user)->_save();
        }

        return $validation->response(
            'Fichier modifié',
            'Impossible de modifier le fichier'
        );
    }
}