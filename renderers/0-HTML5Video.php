<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  fedora-connector
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class HTML5Video_Renderer extends FedoraConnector_AbstractRenderer
{
    // HTML5 video mime types
    private $videoMimeTypes = array('video/mp4', 'video/webm', 'video/ogg');
    
    /**
     * Check of the renderer can handle a given mime type.
     *
     * @param string $mimeType The mimeType.
     * @return boolean True if this can display the datastream.
     */
    function canDisplay($mimeType) {
        return in_array($mimeType, $this->videoMimeTypes) || $mimeType == 'application/xml';
    }

    /**
     * Display an object.
     *
     * @param Omeka_Record $object The Fedora object record.
     * @return DOMDocument The HTML DOM for the datastream.
     */
    function display($object, $params = array()) {
        $dom  = new DOMDocument();
        
        if (isset($params['forceImage']) && $params['forceImage']) {
            foreach (explode(',', $object->dsids) as $dsid) {
                $url = "{$object->getServer()->url}/objects/{$object->pid}" .
                    "/datastreams/{$dsid}/content";

                // Get mime type.
                $mimeType = $object->getServer()->getMimeType(
                    $object->pid, $dsid
                );

                // if a jpeg stream exists then use it
                if ($mimeType == 'image/jpeg') {
                    $imgNode = $dom->createElement('img');
                    $dom ->appendChild($imgNode);
                    $imgNode->setAttribute('src', $url);
                    return $dom;
                }
            }
        }
        
        $videoNode = $dom->createElement('video');
        $dom ->appendChild($videoNode);
        $videoNode->setAttribute('class', 'video-js vjs-default-skin');
        $videoNode->setAttribute('preload', 'auto');
        $videoNode->setAttribute('controls', 'controls');
        $videoNode->setAttribute('width', '640');
        $videoNode->setAttribute('height', '264');
        $videoNode->setAttribute('id', $object->pid);
        $videoNode->setAttribute('data-setup', '{}');
        
        foreach (explode(',', $object->dsids) as $dsid) {
            $url = "{$object->getServer()->url}/objects/{$object->pid}" .
                "/datastreams/{$dsid}/content";

            // Get mime type.
            $mimeType = $object->getServer()->getMimeType(
                $object->pid, $dsid
            );
            
            // if a jpeg stream exists then use it a the "poster"
            if ($mimeType == 'image/jpeg') {
                $videoNode->setAttribute('poster', $url);
            }
            
            // list supported sources
            if (in_array($mimeType, $this->videoMimeTypes)) {
                $sourceNode = $dom->createElement('source');
                $videoNode ->appendChild($sourceNode);
                $sourceNode->setAttribute('src', $url);
                $sourceNode->setAttribute('type', $mimeType);
            }
        }
        
        return $dom;
    }


}
