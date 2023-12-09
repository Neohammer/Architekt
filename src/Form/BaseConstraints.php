<?php

namespace Architekt\Form;

use Architekt\Form\Exceptions\FileUploadErrorException;
use Architekt\Form\Exceptions\FileUploadFailException;
use Architekt\Form\Exceptions\FileUploadRequiredException;
use Architekt\Library\File;
use Architekt\Library\UploadFile;
use Architekt\DB\Entity;

class BaseConstraints
{
    public final static function _autoCheckString(
        ?string $string,
        ?string $regexp = null
    ): ?string
    {
        if (!$string || self::isEmptyString($string)) {
            return null;
        }

        if($regexp && !preg_match(sprintf('|^%s$|',$regexp),$string)){
            return null;
        }

        return strip_tags(trim($string));
    }


    public static function isEmptyString(?string $value): bool
    {
        if($value === null){
            return true;
        }

        return strlen(trim($value)) === 0;
    }

    public static function isEmptyTime(string $time): bool
    {
        return self::isEmptyString($time) || '00:00:00' === $time || '00:00' === $time;
    }

    public static function validateDate(string $date): bool
    {
        return false !== \DateTime::createFromFormat('d/m/Y', $date)
                || false !== \DateTime::createFromFormat('Y-m-d', $date);
    }

    public static function validateDatetime(string $datetime): bool
    {
        return false !== \DateTime::createFromFormat('d/m/Y\TH:i', $datetime)
            || false !== \DateTime::createFromFormat('Y-m-d\TH:i', $datetime);
    }

    public static function dateIsBefore(string $date, string $date_before): bool
    {
        return $date < $date_before;
    }

    public static function validateEmail(string $email): bool
    {
        return !(false === filter_var($email, FILTER_VALIDATE_EMAIL));
    }

    public static function validateInt(?string $int): bool
    {
        return false !== filter_var($int, FILTER_VALIDATE_INT);
    }

    public static function requireEmail(string $email): bool
    {
        return !self::isEmptyString($email) && self::validateEmail($email);
    }

    public static function convertTimeToMinutes(string $time): int
    {
        $tmp = explode(':', $time);
        return (int)$tmp[0] * 60 + (int)$tmp[1];
    }

    public static function convertMinutesToTime(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $minutes -= ($hours * 60);
        return sprintf(
            '%s:%s:00',
            str_pad($hours, 2, '0', STR_PAD_LEFT),
            str_pad($minutes, 2, '0', STR_PAD_LEFT)
        );
    }

    public static function convertPrice(string $price): ?string
    {
        $price = str_replace([',', ' '], ['.', ''], trim($price));
        if (is_numeric($price)) {
            return number_format((float)$price, 2, '.', '');
        }
        return null;
    }

    /**
     * @throws FileUploadErrorException
     * @throws FileUploadFailException
     * @throws FileUploadRequiredException
     * @throws \Architekt\Mysql\Exception
     */
    public static function validateEntityFile(
        Entity $entity,
        File   $fileOriginal,
        string $field,
        string $fileType,
        ?array $uploadFile,
        array  $options = [],
        string $delete = '0'
    ): void
    {
        $deleteOriginalFile = false;
        if ('1' === $delete) {
            $deleteOriginalFile = true;
            $entity->_set($field, null);
        }
        $file = new UploadFile($uploadFile);
        if ($file->requestUpload()) {
            if (!$file->hasBeenUploaded()) {
                ConstraintException::fileUploadError();
            }
            $upload = File::upload($file, $fileType);

            if (null === $upload) {
                ConstraintException::fileUploadFail();
            }
            $deleteOriginalFile = true;
            $entity->_set($field, $upload);
        } else {
            if (
                array_key_exists('required', $options)
                && true === $options['required']
                && (!$fileOriginal->_isLoaded() || false === $deleteOriginalFile)
            ) {
                ConstraintException::fileRequiredError();
            }
        }

        if ($deleteOriginalFile && $fileOriginal->_isLoaded()) {
            //$fileOriginal->delete();
        }
    }

    /**
     * @throws FileUploadErrorException
     * @throws FileUploadFailException
     * @throws FileUploadRequiredException
     * @throws \Architekt\Mysql\Exception
     */
    public static function validateFile(
        File   $file,
        ?array $postedFile,
        array  $options = [],
    ): ?File
    {

        $uploadFile = new UploadFile($postedFile);
        if ($uploadFile->requestUpload()) {
            if (!$uploadFile->hasBeenUploaded()) {
                ConstraintException::fileUploadError();
            }
            $file = File::upload($uploadFile, $file);

            if (null === $file) {
                ConstraintException::fileUploadFail();
            }

            return $file;
        }

        if (
            array_key_exists('required', $options)
            && true === $options['required']
        ) {
            ConstraintException::fileRequiredError();
        }

        return null;
    }
}