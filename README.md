# adis-crud
silverstripe frontend CRUD and search using DataTable

Cara menggunakan:
- copy ke root project silvestripe, nama folder harus "adis-crud"
- dev/build

File yang perlu diedit / disesuaikan:
```sh
code/BrowseController.php 
```
- method **getConfigColumns** --> menambahkan setting kolom yang ditampilkan & yang dicari
- method **windowajax** --> bagian query --> variabel **$sql**
```sh

## Belum lengkap:
- master detail
- action approve reject