<?php

namespace Architekt\Form;

use Architekt\Form\Exceptions\FileUploadErrorException;
use Architekt\Form\Exceptions\FileUploadFailException;
use Architekt\Form\Exceptions\FileUploadRequiredException;

class ConstraintException extends \Exception
{
    /**
     * @throws FileUploadRequiredException
     */
    public static function fileRequiredError(): void
    {
        throw new FileUploadRequiredException("Le fichier est requis");
    }
    /**
     * @throws FileUploadErrorException
     */
    public static function fileUploadError(): void
    {
        throw new FileUploadErrorException("Impossible de téléverser le fichier (ERR:01)");
    }

    /**
     * @throws FileUploadFailException
     */
    public static function fileUploadFail(): void
    {
        throw new FileUploadFailException("Impossible de téléverser le fichier (ERR:02)");
    }
}