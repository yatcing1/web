<html lang="en">
<head>
    <?php $this->load->view("includes/head"); ?>
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
                        <h3>
                            Kategori Düzenle
                        </h3>
                        <hr>
                        <form action="<?php echo base_url("admin/category_update_go/" . $category[0]['parentID']) ?>"
                              method="POST" enctype="multipart/form-data">
                            <ul class="nav nav-tabs nav-tabs-sm nav-overflow">
                                <?php
                                $i = 0;
                                foreach ($category as $nw) {
                                    $i++;
                                    if ($i == 1) {
                                        $act = "active";
                                    } else {
                                        $act = "";
                                    }
                                    ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?= $act; ?>" id="<?= $nw['slug']; ?>-tab" data-toggle="tab"
                                           href="#events-<?= $nw['slug']; ?>" role="tab" aria-controls="home"
                                           aria-selected="true">
                                            <?php if (getErrorType("title", $nw['slug'])) { ?> <i
                                                class="text-danger fe fe-alert-triangle"></i>
                                            <?php } ?>&ensp;<?= $nw['view']; ?>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                            <div class="tab-content" id="myTabContent">
                                <?php
                                $i = 0;
                                foreach ($category as $nw) {
                                    $i++;
                                    if ($i == 1) {
                                        $act = "show active";
                                    } else {
                                        $act = "";
                                    }
                                    ?>
                                    <div class="tab-pane formtab fade pt-4 pb-4 <?= $act; ?>"
                                         id="events-<?= $nw['slug']; ?>"
                                         role="tabpanel" aria-labelledby="<?= $nw['slug']; ?>-tab">

                                        <div class="form-group">
                                            <label class="control-label">Kategori İsmi (<?= $nw['view']; ?>)</label>
                                            <input class="form-control" type="text" name="title[<?= $nw['slug']; ?>]"
                                                   placeholder="Başlık (<?= $nw['view']; ?>)"
                                                   value="<?= $nw['title'] ?>">
                                            <span class="help-block text-danger"
                                                  id="subdesc"><?= getErrorType("title", $nw['slug']) ?></span>
                                            <?php
                                            $uniqFail = $this->session->flashdata("uniqueFailed");
                                            if ($uniqFail) { ?>
                                                <span class="help-danger text-danger">Girdiğiniz Kategori İsmi başka bir Ürünle eşleşiyor. Kategoriye gitmek için
                                        <a href="<?php echo base_url("admin/category_update/" . $uniqFail); ?>">Tıklayın</a>
                                    </span>
                                            <?php } ?>
                                        </div>

                                    </div>
                                <?php } ?>
                            </div>

                            <div class="form-group">
                                <strong class="col-form-label ">
                                    Kategori Görseli
                                </strong>
                                <div>
                                    <img width="200" class="p-2"
                                         src="<?php echo base_url("includes/images/category/" . $category[0]['img']) ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <strong class="col-form-label ">
                                    Görsel Seç
                                </strong>
                                <input type="file" name="img" class="form-control">
                            </div>
                            <?php
                            $getPrdWCatVal = '';
                            if ($category[0]['cat_status'] == 0) {
                                $getPrdWCatVal = 'top_cat_id';
                                $catStatus = 'Üst Kategori';
                            } elseif ($category[0]['cat_status'] == 1) {
                                $getPrdWCatVal = 'mast_cat_id';
                                $catStatus = 'Ana Kategori';
                            } elseif ($category[0]['cat_status'] == 2){
                                $getPrdWCatVal = 'sub_cat_id';
                                $catStatus = 'Alt Kategori';
                            }elseif($category[0]['cat_status'] == 3){
                                $getPrdWCatVal = 'bottom_cat_id';
                                $catStatus = 'En Alt Kategori';
                            }
                            ?>
                            <div class="form-group">
                                <strong class="col-form-label ">
                                    Ürün Sayısı
                                </strong>
                                <input type="text" readonly class="form-control"
                                       value=" <?= getPrdWithCat($category[0]['parentID'], $getPrdWCatVal); ?>">
                            </div>

                            <div class="form-group">
                                <strong class="col-form-label ">
                                    Kategori Durumu
                                </strong>
                                <input type="text" readonly class="form-control"
                                       value="<?= $catStatus ; ?>">
                            </div>


                            <div class="row">
                                <?php foreach ($category as $nw) { ?>
                                    <div class="col-md-6 mt-0 mb-0">
                                        <div class="form-group">
                                            <label class="col-form-label"><?= $nw['view'] . ' Url'; ?></label>
                                            <div class="form-group">
                                                <input readonly class="form-control"
                                                       value="<?= base_url("category/" . $nw['url'] . "-" . $nw['id']) ?>">
                                                <!--   <a target="_blank" class="mt-2"
                                                   href="<?= base_url("category/" . $nw['url'] . "/" . $nw['id']) ?>">Ürüne
                                                    Git</a>-->
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>


                            <div class="form-group ">
                                <strong class="col-form-label ">
                                    Durum
                                </strong>
                                <select class="form-control" name="status">
                                    <option <?php if ($category[0]['status'] == 0) { ?> selected <?php } ?> value="0">
                                        Pasif
                                    </option>
                                    <option <?php if ($category[0]['status'] == 1) { ?> selected <?php } ?> value="1">
                                        Aktif
                                    </option>
                                </select>
                                <button style="margin-top: 25px !important"
                                        class="form-control btn btn-success mt-4">
                                    Kaydet
                                </button>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view("includes/script"); ?>
</body>
</html>