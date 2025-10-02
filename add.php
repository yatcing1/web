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
                            Kategori Ekle
                        </h3>
                        <hr>
                        <?php if ($this->session->flashdata("formError")) { ?>
                            <div class="alert alert-danger">
                                <p><small>İşlem Başarısız. Lütfen Gerekli Alanları Doldurunuz.</small></p>
                            </div>
                        <?php } ?>
                        <form action="<?php echo base_url("admin/category_add_go") ?>" method="POST"
                              enctype="multipart/form-data">
                            <ul class="nav nav-tabs nav-tabs-sm nav-overflow">
                                <?php
                                $i = 0;
                                foreach (langs() as $lang) {
                                    $i++;
                                    if ($i == 1) {
                                        $act = "active";
                                    } else {
                                        $act = "";
                                    }
                                    ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?= $act; ?>" id="<?= $lang['slug']; ?>-tab"
                                           data-toggle="tab"
                                           href="#events-<?= $lang['slug']; ?>" role="tab" aria-controls="home"
                                           aria-selected="true">
                                            <?php if (getErrorType("title", $lang['slug'])) { ?> <i
                                                class="text-danger fe fe-alert-triangle"></i>
                                            <?php } ?>&ensp;<?= $lang['view']; ?>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                            <div class="tab-content" id="myTabContent">
                                <?php
                                $i = 0;
                                foreach (langs() as $lang) {
                                    $i++;
                                    if ($i == 1) {
                                        $act = "show active";
                                    } else {
                                        $act = "";
                                    }
                                    ?>
                                    <div class="tab-pane formtab fade pt-4 pb-4 <?= $act; ?>"
                                         id="events-<?= $lang['slug']; ?>"
                                         role="tabpanel" aria-labelledby="<?= $lang['slug']; ?>-tab">

                                        <div class="form-group">
                                            <label class="control-label">Kategori İsmi (<?= $lang['view']; ?>)</label>
                                            <input class="form-control" type="text" name="title[<?= $lang['slug']; ?>]"
                                                   placeholder="Başlık (<?= $lang['view']; ?>)">
                                            <span class="help-block text-danger"
                                                  id="subdesc"><?= getErrorType("title", $lang['slug']) ?></span>
                                            <?php
                                            $uniqFail = $this->session->flashdata("uniqueFailed");
                                            if ($uniqFail) { ?>
                                                <span class="help-danger text-danger">Girdiğiniz Kategori İsmi başka bir Kategoriyle eşleşiyor. Kategoriye gitmek için
                                        <a href="<?php echo base_url("admin/category_update/" . $uniqFail); ?>">Tıklayın</a>
                                    </span>
                                            <?php } ?>
                                        </div>

                                    </div>
                                <?php } ?>
                            </div>

                            <div class="form-group">
                                <strong class="col-form-label ">
                                    Görsel Seç
                                </strong>
                                <input type="file" name="img" class="form-control">
                            </div>
                            <?php if (getCat(1)) { ?>
                                <div class="form-group d-none" id="mastGroup">
                                    <strong class="col-form-label">
                                        Ana Kategori
                                    </strong>
                                    <select class="form-control" name="mast_cat" id="mastCat">
                                        <?php foreach (getCat("1") as $mastCat) { ?>
                                            <option value="<?= $mastCat->parentID ?>"><?= $mastCat->title ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            <?php } ?>

                            <div class="form-group d-none" id="topGroup">
                                <strong class="col-form-label">
                                    Üst Kategori
                                </strong>
                                <select class="form-control" name="top_cat" id="topCat">
                                    <option value="0" selected>Kategori Seçiniz</option>
                                </select>
                            </div>

                            <div class="form-group d-none" id="subGroup">
                                <strong class="col-form-label">
                                    Alt Kategori
                                </strong>
                                <select class="form-control" name="sub_cat" id="subCat">
                                    <option value="0" selected>Üst Kategori Seçiniz</option>
                                </select>
                            </div>

                            <div class="form-group ">
                                <strong class="col-form-label">
                                    Kategori Durumu
                                </strong>
                                <select class="form-control" name="cat_status" id="changeCat">
                                    <option value="1" selected>Ana Kategori</option>
                                    <option value="0">Üst Kategori</option>
                                    <option value="2">Alt Kategori</option>
                                    <option value="3">En Alt Kategori</option>

                                </select>
                            </div>

                            <div class="form-group ">
                                <strong class="col-form-label">
                                    Durum
                                </strong>
                                <select class="form-control" name="status">
                                    <option value="0">Pasif</option>
                                    <option value="1" selected>Aktif</option>
                                </select>
                                <button style="margin-top: 25px !important" class="form-control btn btn-success mt-4">
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
<script>
    var base = "<?= base_url();?>";

    $('#changeCat').on("change", function () {

        var catid = $(this).find(":selected").val();
        if (catid == 0) {
            $('#mastGroup').attr("class", "form-group d-block");
            $('#topGroup').attr("class", "form-group d-none");
            $('#subGroup').attr("class", "form-group d-none");
            $('subCat').val("");
            $('#topCat').html('<option value="" selected>Kategori Seçiniz</option>');
        } else if (catid == 1) {
            $('#mastGroup').attr("class", "form-group d-none");
            $('#subGroup').attr("class", "form-group d-none");
            $('mastCat').val("");
            $('topCat').val("");
            $('#topGroup').attr("class", "form-group d-none");
            $('#topCat').html('<option value="" selected>Kategori Seçiniz</option>');
        } else if (catid == 2) {
            $('#topGroup').attr("class", "form-group d-block");
            $('#mastGroup').attr("class", "form-group d-block");
            $('#subGroup').attr("class", "form-group d-none");
            $('subCat').val("");
        }else if (catid == 3) {
            $('#topGroup').attr("class", "form-group d-block");
            $('#mastGroup').attr("class", "form-group d-block");
            $('#subGroup').attr("class", "form-group d-block");
        }

    });
        $('#mastCat').on("change", function () {
            var itemid = $(this).find(":selected").val();
            $.ajax({
                url: base + "admin/getTopCat",
                type: "POST",
                data: {id: itemid},
                success: function (response) {
                    $('#topCat').html(response);
                },
                error: function (response) {
                    alert("Bir Hata Oluştu, Lütfen Tekrar Deneyin.");
                }
            });
        });

    $('#topCat').on("change", function () {
        var itemid = $(this).find(":selected").val();
        $.ajax({
            url: base + "admin/getSubCats",
            type: "POST",
            data: {id: itemid},
            success: function (response) {
                $('#subCat').html(response);
            },
            error: function (response) {
                alert("Bir Hata Oluştu, Lütfen Tekrar Deneyin.");
            }
        });
    });

</script>
</body>
</html>