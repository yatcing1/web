<html lang="en">
<head>
    <?php $this->load->view("includes/head") ; ?>
</head>
<body>
<?php $this->load->view("includes/modals") ?>
<?php $this->load->view("includes/navbar") ?>
<div class="main-content">
    <?php $this->load->view("includes/navbar-top"); ?>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="header mt-md-5">
                    <div class="header-body">
                        <div class="row">
                            <div class="col-md-11">
                                Üst Kategoriler
                            </div>
                            <a href="<?php echo base_url('admin/category_add/'); ?>"  class="btn btn-outline btn-primary btn-sm pull-right col-md-1" href=""> <i class="fe fe-plus"></i> Yeni Ekle </a>
                        </div>
                        <hr>
                        <?php
                        if (empty($categories)) {?>
                            <div class="alert alert-danger text-center">
                                <p>Burada Herhangi bir Veri Bulunmamaktadır. Yeni Kategori Eklemek İçin <a href="<?php echo base_url('admin/category_add'); ?>">Tıklayın</a> </p>
                            </div>
                        <?php } else { ?>
                            <div class="card">
                                <div class="table-responsive mb-0">
                                    <table class="table table-sm table-nowrap card-table">
                                        <thead>
                                        <th>ParentID</th>
                                        <th>Başlık</th>
                                        <th>Görsel</th>
                                        <th>Ana Kategori</th>
                                        <th>Ürün Sayısı</th>
                                        <th>Durumu</th>
                                        <th>İşlemler</th>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($categories as $key ){ ?>
                                        <tr>
                                            <td><?php echo $key->parentID; ?></td>
                                            <td><?php echo $key->title; ?></td>
                                            <td>
                                                <div class="avatar avatar-sm p-2">
                                                    <img class="img-fluid avatar-img rounded" width="100" src="<?php echo base_url("includes/images/category/$key->img") ?>">
                                                </div>
                                            </td>
                                            <td>
                                                <?= getMastCat($key->parentCat,"title")?>
                                            </td>
                                            <td>
                                                <?= getPrdWithCat($key->parentID,"top_cat_id"); ?>
                                            </td>
                                            <td align="">
                                                <?php if ($key->status==1) {?>
                                                    <button class="btn btn-success btn-sm">Aktif</button>
                                                <?php }else { ?>
                                                    <button class="btn btn-danger btn-sm">Pasif</button>
                                                <?php } ?>
                                            </td>
                                            <td align="">
                                                <a href="<?= base_url("admin/category_update/$key->parentID") ?>"  class="btn btn-warning btn-sm btn-outline" href=""><i class="fe fe-edit">&ensp;</i>Düzenle</a>
                                                <a href="<?php echo base_url("admin/top_cat_delete/" . $key->parentID); ?>"
                                                   onclick="return confirm('Silmek istediğinize emin misiniz? Eğer silerseniz bağlı ürünlerde silenecek.')"
                                                   class="btn btn-sm btn-danger"><i class="fe fe-trash-2"></i>Sil
                                                </a>
                                            </td>
                                        </tr>
                                        </tbody>
                                        <?php }  ?>
                                    </table>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<?php if($this->session->flashdata("category_update")){ ?>
    <script type="text/javascript">
        iziToast.success({

            title: 'İşlem Başarılı',
            message: 'Kategori Başarıyla Düzenlendi',
            color: 'green',
            position: 'topCenter'
        });

    </script>

<?php } ?>

<?php if($this->session->flashdata("category_delete")){ ?>
    <script type="text/javascript">
        iziToast.success({

            title: 'İşlem Başarılı',
            message: 'Kategori Başarıyla Silindi',
            color: 'green',
            position: 'topCenter'
        });

    </script>

<?php } ?>

<?php if($this->session->flashdata("category_add")){ ?>
    <script type="text/javascript">
        iziToast.success({

            title: 'İşlem Başarılı',
            message: 'Kategori Başarıyla Eklendi',
            color: 'green',
            position: 'topCenter'
        });

    </script>
<?php } ?>

<?php $this->load->view("includes/script") ; ?>
</body>
</html>
