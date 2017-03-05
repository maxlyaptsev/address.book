<?php

use Bitrix\Main\Config\Option;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


// проверим подключен ли модуль
if(!\Bitrix\Main\Loader::includeModule('highloadblock'))
{
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
}

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;

if ($this->StartResultCache(3600))
{
	// получаем нужный hl-блок из параметров или настроек модуля
	$hlblock_id = isset($arParams['BLOCK_ID']) ? $arParams['BLOCK_ID'] : Option::get('lyaptsev.addressbook', "HL_BLOCK_ID");

	// не указан id hl-блока - показываем ошибку
	if (empty($hlblock_id))
	{
		ShowError(GetMessage('HLBLOCK_LIST_NO_ID'));
		return false;
	}

	$hlblock = HL\HighloadBlockTable::getById($hlblock_id)->fetch();

	// получим данные hl-блока
	$entity = HL\HighloadBlockTable::compileEntity($hlblock);
	$entityDataClass = $entity->getDataClass();

	$rows = $entityDataClass::getList(array(
		'select' => array('*')
	))->fetchAll();


	$arResult['ITEMS'] = $rows;


	$this->IncludeComponentTemplate();
}