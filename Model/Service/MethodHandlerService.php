<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Cmsbox\Mercanet\Model\Service;

use Cmsbox\Mercanet\Gateway\Config\Core;

class MethodHandlerService {
    /**
     * @var Reader
     */
    protected $moduleDirReader;

    /**
     * MethodHandlerService constructor.
     */
    public function __construct(
        \Magento\Framework\Module\Dir\Reader $moduleDirReader
    ) {
        $this->moduleDirReader = $moduleDirReader;
    }

    private function getFiles($path) {
        $result = [];
        $flags = \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS;
        $iterator = new \FilesystemIterator($path, $flags);
 
        foreach ($iterator as $file) {
            $fileName = $file->getFilename();
            if (strpos($fileName, '.') !== 0) {
                $name = basename($fileName, '.php');
                $result[$name] = $name;
            }
        }

        return $result;
    }

    /**
     * Build a payment method instance.
     */
    public static function getStaticInstance($methodId) {
        $classPath = "\\" . str_replace('_', "\\", Core::moduleName())
        . "\\Model\\Methods\\" . Core::methodName($methodId);
        if (class_exists($classPath)) {
            return $classPath;
        }

        return false;
    }

    private function getPath() {
        return $this->moduleDirReader->getModuleDir('', Core::moduleName()) . '/Model/Methods';
    }
}
