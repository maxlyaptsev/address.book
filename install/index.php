<?php

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class lyaptsev_addressbook extends CModule
{
    public function __construct()
    {
        $arModuleVersion = array();
        
        include __DIR__ . '/version.php';

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }
        
        $this->MODULE_ID = 'lyaptsev.addressbook';
        $this->MODULE_NAME = Loc::getMessage('MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('MODULE_DESCRIPTION');
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->PARTNER_NAME = Loc::getMessage('MODULE_PARTNER_NAME');
        $this->PARTNER_URI = 'http://1c-bitrix.ru';
    }

    public function doInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->installDB();
        $this->installFiles();
    }

    public function doUninstall()
    {
        $this->unInstallFiles();
        $this->uninstallDB();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    /**
     * Добавляем hl-блок и демо данные
     * @throws Exception
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\SystemException
     */
    public function installDB()
    {
        if (Loader::includeModule($this->MODULE_ID) && Loader::includeModule('highloadblock'))
        {
            //создание hl-блока
            $result = Bitrix\Highloadblock\HighloadBlockTable::add([
                'NAME' => 'AddressBook',
                'TABLE_NAME' => 'lyaptsev_address_book',
            ]);


            if($result->isSuccess())
            {
                $highLoadBlockId = $result->getId();
            }
            else
            {
                throw new \Bitrix\Main\SystemException($result->getErrorMessages());
            }


            if((int)$highLoadBlockId > 0)
            {
                // создание свойств  hl-блока
                $userTypeEntity = new CUserTypeEntity();
                $userTypeDataList = [
                    [
                        'ENTITY_ID'         => 'HLBLOCK_'.$highLoadBlockId,
                        'FIELD_NAME'        => 'UF_FIO',
                        'USER_TYPE_ID'      => 'string',
                    ],
                    [
                        'ENTITY_ID'         => 'HLBLOCK_'.$highLoadBlockId,
                        'FIELD_NAME'        => 'UF_ADDRESS',
                        'USER_TYPE_ID'      => 'string',
                    ],
                    [
                        'ENTITY_ID'         => 'HLBLOCK_'.$highLoadBlockId,
                        'FIELD_NAME'        => 'UF_PHONE',
                        'USER_TYPE_ID'      => 'string',
                    ],
                ];

                foreach ($userTypeDataList as $userTypeData)
                {
                    $userTypeId = $userTypeEntity->Add($userTypeData);
                }

                // записываем в настройки модуля id hl-блока
                \Bitrix\Main\Config\Option::set($this->MODULE_ID, 'HL_BLOCK_ID', $highLoadBlockId);

                // Добавление демо-данных
                $arHLBlock = HighloadBlockTable::getById($highLoadBlockId)->fetch();
                $obEntity = HighloadBlockTable::compileEntity($arHLBlock);
                $strEntityDataClass = $obEntity->getDataClass();

                $demoFieldsList = [
                    [
                        'UF_FIO' => 'Иван Петрович',
                        'UF_ADDRESS' => 'г. Москва, проспект Мира 101',
                        'UF_PHONE' => '+7(920) 123 45 67'
                    ],
                    [
                        'UF_FIO' => 'Василий Иванович',
                        'UF_ADDRESS' => 'г. Тула, ул. Советская 112',
                        'UF_PHONE' => '+7(920) 123 45 67'
                    ]
                ];
                foreach ($demoFieldsList as $demoFields)
                {
                    $result = $strEntityDataClass::add($demoFields);
                }

                if(!$result->isSuccess())
                {
                    throw new \Bitrix\Main\SystemException('Не удалось создать hl-блок: ' . $result->getErrorMessages());
                }

            }
        }
    }

    /**
     * Удаляем hl-блок
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\LoaderException
     */
    public function uninstallDB()
    {
        if (Loader::includeModule($this->MODULE_ID) && Loader::includeModule('highloadblock'))
        {
            // удаляем HighloadBlock
            $highloadBlockID = Option::get($this->MODULE_ID, "HL_BLOCK_ID");
            Bitrix\Highloadblock\HighloadBlockTable::delete($highloadBlockID);
        }
    }

    /**
     * Устанавливаем компонент
     * @return bool
     */
    function installFiles()
    {
        CopyDirFiles(__DIR__ . "/components/lyaptsev/", $_SERVER["DOCUMENT_ROOT"] . "/local/components/lyaptsev", true, true);
        return true;
    }

    /**
     * Удаляем компонент
     * @return bool
     */
    function unInstallFiles()
    {
        DeleteDirFilesEx("/local/components/lyaptsev/addressbook.list");
        return true;
    }
}
