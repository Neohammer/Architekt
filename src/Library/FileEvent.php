<?php

namespace Architekt\Library;

use Events\Delete;
use Events\Display;
use Events\Download;
use Events\EntityEvent;
use Events\Event;

class FileEvent extends EntityEvent
{

    /**
     * @param File $entity
     * @return Event[]
     */
    static public function get($entity): array
    {
        if (null === $entity) {
            return [];
        }
        $actions = [];

        if ($entity->isImage()) {
            $actions[] = new Display(
                sprintf('/Library/display/%s', $entity->_primary()),
                Event::TYPE_BLANK
            );
        } else {
            $actions[] = new Download(
                sprintf('/Library/display/%s', $entity->_primary()),
                Event::TYPE_BLANK
            );

        }

        $actions[] = new Delete(
            sprintf('/Library/delete/%s', $entity->_primary()),
            Event::TYPE_MODAL
        );

        return $actions;
    }

}