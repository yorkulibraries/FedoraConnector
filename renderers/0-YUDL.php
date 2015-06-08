<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  fedora-connector
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class YUDL_Renderer extends FedoraConnector_AbstractRenderer
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
        return in_array($mimeType, $this->videoMimeTypes) 
            || (bool) (preg_match('/^image\/.*/', $mimeType));
    }

    /**
     * Display an object.
     *
     * @param Omeka_Record $object The Fedora object record.
     * @return DOMDocument The HTML DOM for the datastream.
     */
    function display($object, $params = array()) {
        $dom  = new DOMDocument();
        
        if (!$this->hasVideoStream($object) || $params['forceImage']) {
            foreach (explode(',', $object->dsids) as $dsid) {
                // Get mime type.
                $mimeType = $object->getServer()->getMimeType(
                    $object->pid, $dsid
                );
                
                // display first image stream
                if (preg_match('/^image\/.*/', $mimeType)) {
                    $imgNode = $dom->createElement('img');
                    $dom ->appendChild($imgNode);
                    $imgNode->setAttribute('src', $this->getDatastreamURL($object, $dsid));
                    $imgNode->setAttribute('data-item-id', $object->item_id);
                    $imgNode->setAttribute('class', 'fedora-renderer');
                    $imgNode->setAttribute('alt', metadata($object->getItem(), array('Dublin Core', 'Title')));
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
        $videoNode->setAttribute('data-item-id', $object->item_id);
        
        foreach (explode(',', $object->dsids) as $dsid) {
            // Get mime type.
            $mimeType = $object->getServer()->getMimeType(
                $object->pid, $dsid
            );
            
            // if a TN stream exists then use it as the "poster"
            if ($dsid == 'TN') {
                $videoNode->setAttribute('poster', $this->getDatastreamURL($object, $dsid));
            }
            
            // list supported sources
            if (in_array($mimeType, $this->videoMimeTypes)) {
                $sourceNode = $dom->createElement('source');
                $videoNode ->appendChild($sourceNode);
                $sourceNode->setAttribute('src', $this->getDatastreamURL($object, $dsid));
                $sourceNode->setAttribute('type', $mimeType);
            }
        }
        
        return $dom;
    }

    private function hasVideoStream($object) {
        foreach (explode(',', $object->dsids) as $dsid) {
            // Get mime type.
            $mimeType = $object->getServer()->getMimeType(
                $object->pid, $dsid
            );
            if (in_array($mimeType, $this->videoMimeTypes)) {
                return true;
            }
        }
        return false;
    }
    
    private function getDatastreamURL($object, $dsid) {
        $path = "/islandora/object/{$object->pid}/datastream/{$dsid}/view";
        $parts = parse_url($object->getServer()->url);
        $url = "https://{$parts['host']}{$path}";
        return $url;
    }
}
