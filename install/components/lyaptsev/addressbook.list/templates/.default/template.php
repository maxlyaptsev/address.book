<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); ?>


<?
$this->setFrameMode(true);

if (empty($arResult['ITEMS']))
{
	echo 'В адресной книге нет записей';
	return false;
}
?>

<div>Имя сайта: <?=Bitrix\Main\Config\Option::get('main', "site_name")?></div>
<div class="address-book">
	<? foreach ($arResult['ITEMS'] as $item) :?>
		<div class="address-book-item">
			<div class="address-book-item__fio"><?= $item['UF_FIO']?></div>
			<div class="address-book-item__address"><?= $item['UF_ADDRESS']?></div>
			<div class="address-book-item__phone"><?= $item['UF_PHONE']?></div>
		</div>
	<?endforeach?>
</div>