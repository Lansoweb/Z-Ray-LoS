<?php

namespace Los;

class LosZray
{
    public function storeLog($context, &$storage)
    {
        $msg = $context["functionArgs"][0];
        list($usec, $sec) = explode(" ", microtime());
        $date = date("Y-m-d H:i:s", $sec).substr($usec, 1);
        $storage['LosLog'][] = array('date' => $date, 'message' => $msg);
    }

    public function storeDomain($context, &$storage)
    {
        $domainService = $context["returnValue"];
        $storage['LosDomain'][] = [
            $domainService->getDomain() => [
                'domain' => $domainService->getDomain(),
                'layout' => $domainService->getLayout(),
                'config' => $domainService->getDomainOptions()
            ],
        ];
    }

    public function storeLicense($context, &$storage)
    {
        $license = $context["returnValue"];
        $storage['LosLicense'][] = [
            'license' => $license
        ];
    }

    public function storeLicenseValidator($context, &$storage)
    {
        $validator = $context["returnValue"];
        $storage['LosLicense'] = [
            [
                'isLicensed' => $validator->isLicensed(),
                'license' => $validator->getServiceLocator()->get('loslicense.license')
            ]
        ];
    }
}

$losStorage = new \Los\LosZray();
$loslog = new \ZRayExtension("los");
$loslog->setMetadata(array(
    'logo' => __DIR__.DIRECTORY_SEPARATOR.'logo.png',
));
$loslog->setEnabledAfter('Zend\Mvc\Application::init');
$loslog->traceFunction("LosLog\\Log\\StaticLogger::save",  array($losStorage, 'storeLog'), function () {});
$loslog->traceFunction("LosDomain\\Service\\Domain::setServiceLocator", function () {}, array($losStorage, 'storeDomain'));
//$loslog->traceFunction("LosLicense\\Options\\ModuleOptions::getLicense", function () {}, array($losStorage, 'storeLicense'));
$loslog->traceFunction("LosLicense\\Service\\ValidatorService::setServiceLocator", function () {}, array($losStorage, 'storeLicenseValidator'));
