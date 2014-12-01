# Pagination Documentation
PHP pagination library

預設使用$_GET['page'] 作選擇頁面參數

## Functions
- initialize($param = array()) `初始化設定參數`
- set_total($total_page) `設定總頁數 (required)`
- set_limit($link_display) `設定顯示頁數上限 (optional)`
- set_page($cur_page) `設定目前所在頁數 (required)`
- create_link() `取得分頁連結列`

## Example

```php
// require library
// ...

$curPage = ($_GET['page'])?$_GET['page']:1;
$pagination = new PaginationFive();
$pagination->set_total(10);
$pagination->set_page($cur_page);

echo $pagination->create_link();
```
