<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Object table tests.
 *
 * @package     omeka
 * @subpackage  fedoraconnector
 * @author      Scholars' Lab <>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2012 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */

class FedoraConnectorObjectTableTest extends FedoraConnector_Test_AppTestCase
{

    /**
     * findByItem() should get the object for an item.
     *
     * @return void.
     */
    public function testFindByItemWhenRecordExists()
    {

        // Create item and object.
        $item = $this->__item();
        $object = $this->__object($item);

        // Retrieve.
        $retrievedObject = $this->objectsTable->findByItem($item);
        $this->assertEquals($retrievedObject->id, $object->id);

    }

    /**
     * findByItem() should should return false when no object exists.
     *
     * @return void.
     */
    public function testFindByItemWhenNoRecordExists()
    {

        // Create item and object.
        $item = $this->__item();

        // Try to get out an object.
        $this->assertFalse($this->objectsTable->findByItem($item));

    }

}