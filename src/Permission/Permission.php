<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Permission;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;
use Samshal\Acl\Acl;

/**
 * class Permission.
 *
 * Permission Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class Permission
{
    private $acl;
    private $aclLocation = "bin/data/acl";

    public function __construct()
    {
        $aclString = file_get_contents($this->aclLocation);
        $decodedAclString = base64_decode($aclString);

        $this->acl = unserialize($decodedAclString);
    }

    protected function saveAcl()
    {
        $serializedAcl = serialize($this->acl);
        $encodedAclString = base64_encode($serializedAcl);

        file_put_contents($this->aclLocation, $encodedAclString);
    }

    protected function formatObject($object)
    {
        return str_replace(" ", "", strtolower($object));
    }

    public function add(string $type, string $value)
    {
        $addMethod = "add".ucfirst(strtolower($type));
        $this->acl->$addMethod($value);

        $this->saveAcl();
    }

    public function setPermission(string $role, string $permission, string $resource, bool $status)
    {
        // $this->acl->allow(self::formatObject($role), self::formatObject($permission), self::formatObject($resource), $status);
        $this->acl->allow($role, $permission, $resource, $status);


        $this->saveAcl();
    }

    public function setInheritance(string $parent, string $child)
    {
        $child = self::formatObject($child);
        $this->acl->$child->inherits(self::formatObject($parent));

        $this->saveAcl();
    }


    public function getPermission(string $role, string $permission, string $resource)
    {
        return $this->acl->getPermissionStatus($role, $permission, $resource);
    }

    public function getResources()
    {
        return $this->acl->getResources();
    }

    public function getAllPermissions(string $role)
    {
        return $this->acl->globalRegistry->get($role);
    }

    public function removeRole(string $role)
    {
        return $this->acl->roleRegistry->remove($role);
    }
}