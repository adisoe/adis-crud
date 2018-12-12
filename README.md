# adis-crud
silverstripe frontend CRUD and search using DataTable

## Penting:
Library yang dibutuhkan:
- silverstripe bootstrap form https://github.com/unclecheese/silverstripe-bootstrap-forms
- jquery DataTable 

Cara menggunakan:
- copy ke root project silvestripe, nama folder harus "adis-crud"
- dev/build

File yang perlu diedit / disesuaikan:
```sh
code/CrudPage.php 
```
- method **getCustomColumns** --> menambahkan setting kolom yang ingin muncul di add / edit form & data table

## Belum lengkap:
- master detail
- action approve reject