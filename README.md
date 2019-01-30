# adis-crud
silverstripe frontend CRUD and search using DataTable

## Penting:
Library yang dibutuhkan:
- silverstripe bootstrap form https://github.com/unclecheese/silverstripe-bootstrap-forms
- jquery DataTable 
- jquery AutoNumeric https://plugins.jquery.com/autoNumeric/
- boostrap datetimepicker http://eonasdan.github.io/bootstrap-datetimepicker/

Cara menggunakan:
- copy ke root project silvestripe, nama folder harus "adis-crud"
- dev/build

File yang perlu diedit / disesuaikan:
```sh
code/CrudPage.php 

atau "find" comment "SETTING INI"
```
- method **getCustomColumns** --> menambahkan setting kolom yang ingin muncul di add / edit form & data table

## PHP Active Record
- 

## TODO:
- [x] master detail
- [x] action approve reject
- [x] search link blm bisa diklik
- [x] search link blm ada tombol add
- [x] check require field
- [x] datepicker
- [x] autonumeric
- [x] dropdown
- [ ] field di grid & field di form bisa beda 
- [ ] test di mysql other
- [ ] test di sql server