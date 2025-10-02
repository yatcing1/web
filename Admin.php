<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Csv;


class Admin extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library("form_validation");
        $this->load->model("general_settings_model");
        $this->load->model("slider_model");
        $this->load->model("services_model");
        $this->load->model("references_model");
        $this->load->model("category_model");
		$this->load->model("Popup_model");
		$this->load->helper('excel_offer');
			generate_excel_and_send_email($order_id);
		$this->load->library('session');
        $this->load->helper('url');
        if (!get_active_user()) {
            redirect(base_url("adminlogin/"));
        }
    }

    public function index()
    {
        $data['general'] = $this->general_settings_model->get_all();
        $this->load->view('admin/index', $data);
    }

    public function settings()
    {
        $settings = $this->general_settings_model->get(array("id" => 0));
        $data['settings'] = $settings;
        $this->load->view("admin/settings/index", $data);
    }

    public function settings_update()
    {
        if (isset($_GET['redirect']) || $_GET['redirect'] == "check") {
            $str_update = $this->general_settings_model->uptade(
                array("id" => 0),
                array(
                    "title" => strip_tags($this->input->post("title")),
                    "tel" => strip_tags($this->input->post("tel")),
                    "mail" => strip_tags($this->input->post("mail")),
                    "description" => strip_tags($this->input->post("description")),
                    "keywords" => strip_tags($this->input->post("keywords")),
                    "map" => $this->input->post("map"),
                    "address" => strip_tags($this->input->post("address")),
                    "youtube_link"=>strip_tags($this->input->post("youtube_link")),
                    "twitter_link"=>strip_tags($this->input->post("twitter_link")),
                    "facebook_link"=>strip_tags($this->input->post("facebook_link")),
                    "instagram_link"=>strip_tags($this->input->post("instagram_link"))
                )
            );

            $config["allowed_types"] = "jpg|jpeg|png";
            $config["upload_path"] = "includes/images/logo/";
            $this->load->library("upload", $config);
            $upload_logo = $this->upload->do_upload("logo_img");
            if ($upload_logo) {
                $uploaded_file = $this->upload->data("file_name");
                $this->general_settings_model->uptade(
                    array("id" => 0),
                    array("logo_img" => $uploaded_file)
                );
            }
            if ($str_update || $upload_logo || $upload_title) {
                $this->session->set_flashdata("update", "success");
                redirect(base_url("admin/settings"));
            }
        } else {
            redirect(base_url("admin"));
        }
    }
	
	public function product_delete($parentID)
{
    $deleted = $this->db->where('parentID', $parentID)->delete('products');
    $data = [];
    if ($deleted) {
        $data['result'] = 'success';
    } else {
        $data['result'] = 'fail';
    }
    $this->load->view('admin/products/delete', $data);
}
	
// Çoklu dilde ürün kopyalama fonksiyonu
public function product_copy($parentID)
{
    $this->load->model('Product_model');
    // Orijinal ürünlerin hepsini (4 dilde) çek
    $originals = $this->db->where('parentID', $parentID)->order_by('id', 'ASC')->get('products')->result();

    if (count($originals) !== 4) {
        echo "Kopyalanacak ürün 4 dilde eksik!";
        return;
    }

    // Yeni parentID üret
    $new_parentID = $this->generate_random_string(20);

    // Dilleri sırayla işle
    $langs = ['tr', 'en', 'ru', 'ar'];
    foreach ($originals as $key => $product) {
        $new_product = array(
            'parentID'      => $new_parentID,
            'mast_cat_id'   => $product->mast_cat_id,
            'top_cat_id'    => $product->top_cat_id,
            'sub_cat_id'    => $product->sub_cat_id,
            'bottom_cat_id' => $product->bottom_cat_id,
            'title'         => $product->title . ' (Kopya)',
            'img'           => $product->img,
            'description'   => $product->description,
            'dimensions'    => $product->dimensions,
            'slug'          => $langs[$key], // Dil bilgisi burada ayarlanıyor!
            'view'          => $product->view,
            'url'           => $product->url,
            'status'        => $product->status,
            'createdAt'     => date('Y-m-d H:i:s')
        );

        $this->Product_model->insert_product($new_product);
    }

    $this->session->set_flashdata('product_add', 'Ürün 4 dilde başarıyla kopyalandı.');
    redirect('admin/products'); // Ürünler listesine yönlendirir
}

// Bunu controller'ın altına ekleyin!
private function generate_random_string($length = 20)
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $result = '';
    for ($i = 0; $i < $length; $i++) {
        $result .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $result;
}

    public function slider()
    {
        $this->db->where("slug", "tr");
        $data['slider'] = $this->slider_model->get_all();

        $this->load->view("admin/slider/index", $data);
    }

    public function slider_add()
    {
        $this->load->view("admin/slider/add");
    }

    public function slider_add_go()
    {
        $this->session->set_flashdata("randKeyOne", random_string(20));
        $this->session->set_flashdata("randKeyTwo", random_string(20));
        $randKeyOne = $this->session->flashdata("randKeyOne");
        $randKeyTwo = $this->session->flashdata("randKeyTwo");
        $this->db->where("parentID", $randKeyOne);
        $randKeyQuery = $this->db->get("slider")->row();
        $content = $this->input->post("content");

        $config["allowed_types"] = "jpg|jpeg|png|svg";
        $config["upload_path"] = "includes/images/slider";
        $this->load->library("upload", $config);
        $upload = $this->upload->do_upload("img");
        $uploaded_file = $this->upload->data("file_name");


        foreach (langs() as $langs) {
            $addData['caption'] = $content[$langs['slug']];
            $addData['slug'] = $langs['slug'];
            $addData['view'] = $langs['view'];
            $addData['img'] = $uploaded_file;
            $addData['status'] = strip_tags($this->input->post("status"));
            $addData['createdAt'] = date("d-m-Y H:i:s");
            if ($randKeyQuery) {
                $addData['parentID'] = $randKeyTwo;
            } else {
                $addData['parentID'] = $randKeyOne;
            }
            $insert = $this->db->insert("slider", $addData);
        }
        if ($insert) {
            $this->session->set_flashdata("slider_add", "success");
            redirect(base_url("admin/slider"));

        }


    }

    public function slider_update($id = "")
    {
        $slide = $this->db->get_where("slider", array("parentID" => $id))->result_array();
        if (!$id || empty($slide)) {
            redirect(base_url("admin"));
        } else {
            $data['slide'] = $slide;
            $this->load->view("admin/slider/update", $data);
        }
    }

    public function slider_update_go($id = "")
    {
        $updateDatas = $this->db->get_where("slider", array("parentID" => $id))->result_array();
        $updateGroup = array($updateDatas[0]['id'], $updateDatas[1]['id'], $updateDatas[2]['id'], $updateDatas[3]['id']);
        if (!$id || empty($updateDatas)) {
            redirect(base_url("admin"));
        } else {
            $content = $this->input->post("content");
            $config["allowed_types"] = "jpg|jpeg|png|svg";
            $config["upload_path"] = "includes/images/slider";
            $this->load->library("upload", $config);
            $upload = $this->upload->do_upload("img");


            foreach (langs() as $langs) {
                ${"updateData" . $langs['slug']}['caption'] = $content[$langs['slug']];
                ${"updateData" . $langs['slug']}['status'] = strip_tags($this->input->post("status"));

                if ($upload) {
                    $uploaded_file = $this->upload->data("file_name");
                    ${"updateData" . $langs['slug']}['img'] = $uploaded_file;
                }
            }
            $this->db->where("id", $updateGroup[0]);
            $this->db->update("slider", ${"updateData" . "tr"});
            $this->db->where("id", $updateGroup[1]);
            $this->db->update("slider", ${"updateData" . "en"});
            $this->db->where("id", $updateGroup[2]);
            $this->db->update("slider", ${"updateData" . "ru"});
            $this->db->where("id", $updateGroup[3]);
            $update = $this->db->update("slider", ${"updateData" . "ar"});
            if ($update) {
                $this->session->set_flashdata("slider_update", "success");
                redirect(base_url("admin/slider"));
            }

        }
    }

    public function slider_delete($id = "")
    {
        $deletedData = $this->slider_model->get(array("id" => $id));
        if (empty($deletedData)) {
            redirect(base_url("admin"));
        } else {
            $delete = $this->slider_model->delete(array("id" => $id));
            if ($delete) {
                $this->session->set_flashdata("slider_delete", "success");
                redirect(base_url("admin/slider"));
            }
        }
    }

    public function master_categories()
    {
        $this->db->where("cat_status", 1);
        $this->db->where("parentCat", NULL);
        $this->db->where("slug", "tr");
        $data['categories'] = $this->db->get("category")->result();
        $this->load->view("admin/categories/master_categories", $data);
    }

    public function top_categories()
    {
        $this->db->where("cat_status", 0);
        $this->db->where("parentCat !=", NULL);
        $this->db->where("slug", "tr");
        $data['categories'] = $this->db->get("category")->result();
        $this->load->view("admin/categories/top_categories", $data);
    }

    public function sub_categories()
    {
        $this->db->where("cat_status", 2);
        $this->db->where("parentCat !=", NULL);
        $this->db->where("parentTopCat !=", NULL);
        $this->db->where("slug", "tr");
        $data['categories'] = $this->db->get("category")->result();
        $this->load->view("admin/categories/sub_categories", $data);
    }

    public function bottom_categories()
    {
        $this->db->where("cat_status", 3);
        $this->db->where("parentCat !=", NULL);
        $this->db->where("parentTopCat !=", NULL);
        $this->db->where("parentSubCat !=", NULL);
        $this->db->where("slug", "tr");
        $data['categories'] = $this->db->get("category")->result();
        $this->load->view("admin/categories/bottom_categories", $data);
    }

    public function category_add()
    {
        $this->load->view("admin/categories/add");
    }

    public function category_add_go()
    {
        $this->form_validation->set_rules("title[tr]", "Başlık TR", "trim|required");
        $this->form_validation->set_rules("title[en]", "Başlık EN", "trim|required");
        $this->form_validation->set_rules("title[ru]", "Başlık RU", "trim|required");
        $this->form_validation->set_rules("title[ar]", "Başlık AR", "trim|required");
        $this->form_validation->set_message(array("required" => "{field} Boş Olamaz"));
        if ($this->form_validation->run()) {
            $catSt = $this->input->post("cat_status");
            if ($catSt == 0 || $catSt == 1 || $catSt == 2 || $catSt == 3) {
                $this->session->set_flashdata("randKeyOne", random_string(20));
                $this->session->set_flashdata("randKeyTwo", random_string(20));
                $randKeyOne = $this->session->flashdata("randKeyOne");
                $randKeyTwo = $this->session->flashdata("randKeyTwo");
                $this->db->where("parentID", $randKeyOne);
                $randKeyQuery = $this->db->get("category")->row();
                $title = $this->input->post("title");

                $config["allowed_types"] = "jpg|jpeg|png|svg";
                $config["upload_path"] = "includes/images/category";
                $this->load->library("upload", $config);
                $upload = $this->upload->do_upload("img");
                $uploaded_file = $this->upload->data("file_name");

                foreach (langs() as $langs) {
                    $dbCheck = $this->db->get_where("category", array("url" => strip_tags(convertToSEO(strip_tags($title[$langs['slug']])))))->row_array();
                    if ($dbCheck) {
                        $this->session->set_flashdata("uniqueFailed", $dbCheck['parentID']);
                        redirect(base_url("admin/category_add"));
                        exit();
                    }
                }

                foreach (langs() as $langs) {
                    $addData['title'] = strip_tags($title[$langs['slug']]);
                    if ($catSt == 1) {
                        $addData['parentCat'] = NULL;
                        $addData['parentTopCat'] = NULL;
                        $addData['parentSubCat'] = NULL;
                    } elseif ($catSt == 2) {
                        $addData['parentTopCat'] = $this->input->post("top_cat");
                        $addData['parentCat'] = $this->input->post("mast_cat");
                        $addData['parentSubCat'] = NULL;
                    } elseif ($catSt == 0) {
                        $addData['parentCat'] = $this->input->post("mast_cat");
                        $addData['parentTopCat'] = NULL;
                        $addData['parentSubCat'] = NULL;
                    } elseif ($catSt == 3) {
                        $addData['parentTopCat'] = $this->input->post("top_cat");
                        $addData['parentCat'] = $this->input->post("mast_cat");
                        $addData['parentSubCat'] = $this->input->post("sub_cat");
                    }
                    $addData['slug'] = $langs['slug'];
                    $addData['view'] = $langs['view'];
                    $addData['cat_status'] = strip_tags($this->input->post("cat_status"));
                    $addData['img'] = $uploaded_file;
                    $addData['url'] = convertToSeo(strip_tags($title[$langs['slug']]));
                    $addData['status'] = strip_tags($this->input->post("status"));
                    $addData['createdAt'] = date("d-m-Y H:i:s");
                    if ($randKeyQuery) {
                        $addData['parentID'] = $randKeyTwo;
                    } else {
                        $addData['parentID'] = $randKeyOne;
                    }
                    $insert = $this->db->insert("category", $addData);
                }
                if ($insert) {
                    $this->session->set_flashdata("category_add", "success");
                    if ($catSt == 0) {
                        redirect(base_url("admin/top_categories"));
                    } elseif ($catSt == 1) {
                        redirect(base_url("admin/master_categories"));
                    } elseif ($catSt == 2) {
                        redirect(base_url("admin/sub_categories"));
                    } elseif ($catSt == 3) {
                        redirect(base_url("admin/bottom_categories"));
                    }
                }
            } else {
                redirect(base_url("admin"));
            }
        } else {
            $error = $this->form_validation->error_array();
            $this->session->set_flashdata("formError", $error);
            redirect(base_url("admin/category_add"));
        }
    }

    public function category_update($id = null)
    {

        $category = $this->db->order_by("id", "ASC")->get_where("category", array("parentID" => $id))->result_array();
        if (!$id || empty($category)) {
            redirect(base_url("admin"));
        } else {
            $data['category'] = $category;
            $this->load->view("admin/categories/update", $data);
        }
    }

    public function category_update_go($id = null)
    {
        $updateDatas = $this->db->order_by("id", "ASC")->get_where("category", array("parentID" => $id))->result_array();
        $updateGroup = array($updateDatas[0]['id'], $updateDatas[1]['id'], $updateDatas[2]['id'], $updateDatas[3]['id']);
        if (!$id || empty($updateDatas)) {
            redirect(base_url("admin"));
        } else {
            $this->form_validation->set_rules("title[tr]", "Başlık TR", "trim|required");
            $this->form_validation->set_rules("title[en]", "Başlık EN", "trim|required");
            $this->form_validation->set_rules("title[ru]", "Başlık RU", "trim|required");
            $this->form_validation->set_rules("title[ar]", "Başlık Ar", "trim|required");
            $this->form_validation->set_message(array("required" => "{field} Boş Olamaz"));
            $title = $this->input->post("title");

            $config["allowed_types"] = "jpg|jpeg|png|svg";
            $config["upload_path"] = "includes/images/category/";
            $this->load->library("upload", $config);
            $upload = $this->upload->do_upload("img");

            if ($this->form_validation->run()) {
                foreach (langs() as $langs) {
                    $this->db->where("parentID !=", $id);
                    $dbCheck = $this->db->get_where("category", array("url" => strip_tags(convertToSEO(strip_tags($title[$langs['slug']])))))->row_array();
                    if ($dbCheck) {
                        $this->session->set_flashdata("uniqueFailed", $dbCheck['parentID']);
                        redirect(base_url("admin/category_update/$id"));
                        exit();
                    }
                }

                foreach (langs() as $langs) {
                    if ($upload) {
                        $uploaded_file = $this->upload->data("file_name");
                        ${"updateData" . $langs['slug']}['img'] = $uploaded_file;
                    }

                    ${"updateData" . $langs['slug']}['title'] = strip_tags($title[$langs['slug']]);
                    ${"updateData" . $langs['slug']}['url'] = convertToSeo(strip_tags($title[$langs['slug']]));
                    ${"updateData" . $langs['slug']}['status'] = strip_tags($this->input->post("status"));

                }
                $this->db->where("id", $updateGroup[0]);
                $this->db->update("category", ${"updateData" . "tr"});
                $this->db->where("id", $updateGroup[1]);
                $this->db->update("category", ${"updateData" . "en"});
                $this->db->where("id", $updateGroup[2]);
                $this->db->update("category", ${"updateData" . "ru"});
                $this->db->where("id", $updateGroup[3]);
                $update = $this->db->update("category", ${"updateData" . "ar"});
                if ($update) {
                    $this->session->set_flashdata("category_update", "success");
                    if ($updateDatas[0]['cat_status'] == 0) {
                        redirect(base_url("admin/top_categories"));
                    } elseif ($updateDatas[0]['cat_status'] == 1) {
                        redirect(base_url("admin/master_categories"));
                    } elseif ($updateDatas[0]['cat_status'] == 2) {
                        redirect(base_url("admin/sub_categories"));
                    } elseif ($updateDatas[0]['cat_status'] == 3) {
                        redirect(base_url("admin/bottom_categories"));
                    }

                }
            } else {
                $error = $this->form_validation->error_array();
                $this->session->set_flashdata("formError", $error);
                redirect(base_url("admin/category_update/" . $id));
            }
        }
    }

    public function mast_cat_delete($id = null)
    {
        $deletedData = $this->db->get_where("category", array("parentID" => $id, "cat_status" => 1))->result();
        if (!$id || empty($deletedData)) {
            redirect(base_url());
        } else {
            if ($deletedData) {
                $this->db->delete("category", array("parentID" => $id));
                $deletedSub = $this->db->get_where("category", array("parentCat" => $id));
                $deletedProduct = $this->db->get_where("products", array("mast_cat_id" => $id));
                if ($deletedSub) {
                    $this->db->delete("category", array("parentCat" => $id));
                } elseif ($deletedProduct) {
                    $this->db->delete("products", array("mast_cat_id" => $id));
                }
                $this->session->set_flashdata("category_delete", "success");
                redirect(base_url("admin/master_categories"));
            } else {
                redirect(base_url("admin"));
            }
        }
    }

    public function top_cat_delete($id = null)
    {
        $deletedData = $this->db->get_where("category", array("parentID" => $id, "cat_status" => 0))->result();
        if (!$id || empty($deletedData)) {
            redirect(base_url("admin"));
        } else {
            $deletedProduct = $this->db->get_where("products", array("top_cat_id" => $id));
            if ($deletedProduct) {
                $this->db->delete("products", array("top_cat_id" => $id));
            }
            $delete = $this->db->delete("category", array("parentID" => $id));
            if ($delete) {
                $this->session->set_flashdata("category_delete", "success");
                redirect(base_url("admin/top_categories"));
            }
        }
    }


    public function sub_cat_delete($id = null)
    {
        $deletedData = $this->db->get_where("category", array("parentID" => $id, "cat_status" => 2))->result();
        if (!$id || empty($deletedData)) {
            redirect(base_url("admin"));
        } else {
            $deletedProduct = $this->db->get_where("products", array("sub_cat_id" => $id));
            if ($deletedProduct) {
                $this->db->delete("products", array("sub_cat_id" => $id));
            }
            $delete = $this->db->delete("category", array("parentID" => $id));
            if ($delete) {
                $this->session->set_flashdata("category_delete", "success");
                redirect(base_url("admin/sub_categories"));
            }
        }
    }

    public function bottom_cat_delete($id = null)
    {
        $deletedData = $this->db->get_where("category", array("parentID" => $id, "cat_status" => 3))->result();
        if (!$id || empty($deletedData)) {
            redirect(base_url("admin"));
        } else {
            $deletedProduct = $this->db->get_where("products", array("bottom_cat_id" => $id));
            if ($deletedProduct) {
                $this->db->delete("products", array("bottom_cat_id" => $id));
            }
            $delete = $this->db->delete("category", array("parentID" => $id));
            if ($delete) {
                $this->session->set_flashdata("category_delete", "success");
                redirect(base_url("admin/bottom_categories"));
            }
        }
    }


public function products($page = 0)
{
    $this->load->library('pagination');
    $per_page = 11;

    // Toplam ürün sayısı
    $this->db->where("slug", "tr");
    $total_rows = $this->db->count_all_results("products");

    // Pagination ayarları
    $config['base_url'] = base_url('admin/products');
    $config['total_rows'] = $total_rows;
    $config['per_page'] = $per_page;
    $config['uri_segment'] = 3;
    $config['full_tag_open'] = '<ul class="pagination">';
    $config['full_tag_close'] = '</ul>';
    $config['num_tag_open'] = '<li class="page-item">';
    $config['num_tag_close'] = '</li>';
    $config['cur_tag_open'] = '<li class="page-item active"><a href="#" class="page-link">';
    $config['cur_tag_close'] = '</a></li>';
    $config['next_tag_open'] = '<li class="page-item">';
    $config['next_tag_close'] = '</li>';
    $config['prev_tag_open'] = '<li class="page-item">';
    $config['prev_tag_close'] = '</li>';
    $config['attributes'] = array('class' => 'page-link');

    $this->pagination->initialize($config);

    // Sayfalanmış ürünleri çek
    $this->db->where("slug", "tr");
    $this->db->order_by("id", "DESC");
    $products = $this->db->get("products", $per_page, $page)->result();

    $data['products'] = $products;
    $data['links'] = $this->pagination->create_links();

    $this->load->view("admin/products/index", $data);
}

    public function product_add()
    {
        $this->load->view("admin/products/add");
    }

    public function product_add_go()
    {
        $this->form_validation->set_rules("title[tr]", "Başlık TR", "trim|required");
        $this->form_validation->set_rules("title[en]", "Başlık EN", "trim|required");
        $this->form_validation->set_rules("title[ru]", "Başlık RU", "trim|required");
        $this->form_validation->set_rules("title[ar]", "Başlık AR", "trim|required");
        $this->form_validation->set_rules("mast_cat", "Ana Kategori", "trim|required");
        $this->form_validation->set_rules("top_cat", "Üst Kategori", "trim|required");
        $this->form_validation->set_rules("sub_cat", "Alt Kategori", "trim|required");
        $this->form_validation->set_rules("bottom_cat", "En Alt Kategori", "trim|required");
        $this->form_validation->set_message(array("required" => "{field} Boş Olamaz"));

        if ($this->form_validation->run()) {
            if ($this->input->post("mast_cat") == 0) {
                //  $this->session->set_flashdata("mastFail", "Ana Kategori Boş Olamaz");
                // redirect(base_url("admin/product_add"));
                //exit();
                var_dump($this->input->post("mast_cat"));
            }
            $this->session->set_flashdata("randKeyOne", random_string(20));
            $this->session->set_flashdata("randKeyTwo", random_string(20));
            $randKeyOne = $this->session->flashdata("randKeyOne");
            $randKeyTwo = $this->session->flashdata("randKeyTwo");
            $this->db->where("parentID", $randKeyOne);
            $randKeyQuery = $this->db->get("products")->row();
            $title = $this->input->post("title");
            $content = $this->input->post("content");
            $dimensions = $this->input->post("dimensions");

            $config["allowed_types"] = "jpg|jpeg|png|svg";
            $config["upload_path"] = "includes/images/product";
            $this->load->library("upload", $config);
            $upload = $this->upload->do_upload("img");
            $uploaded_file = $this->upload->data("file_name");

            foreach (langs() as $langs) {
                $dbCheck = $this->db->get_where("products", array("url" => strip_tags(convertToSEO(strip_tags($title[$langs['slug']])))))->row_array();
                if ($dbCheck) {
                    $this->session->set_flashdata("uniqueFailed", $dbCheck['id']);
                    redirect(base_url("admin/product_add"));
                    exit();
                }
            }

            foreach (langs() as $langs) {

                $addData['title'] = strip_tags($title[$langs['slug']]);
                $addData['description'] = $content[$langs['slug']];
                $addData['dimensions'] = strip_tags($dimensions[$langs['slug']]);
                $addData['slug'] = $langs['slug'];
                $addData['view'] = $langs['view'];
                $addData['mast_cat_id'] = strip_tags($this->input->post("mast_cat"));
                $addData['top_cat_id'] = strip_tags($this->input->post("top_cat"));
                $addData['sub_cat_id'] = strip_tags($this->input->post("sub_cat"));
                $addData['bottom_cat_id'] = strip_tags($this->input->post("bottom_cat"));
                $addData['img'] = $uploaded_file;
                $addData['url'] = convertToSeo(strip_tags($title[$langs['slug']]));
                $addData['status'] = strip_tags($this->input->post("status"));
                $addData['createdAt'] = date("d-m-Y H:i:s");
                if ($randKeyQuery) {
                    $addData['parentID'] = $randKeyTwo;
                } else {
                    $addData['parentID'] = $randKeyOne;
                }
                $insert = $this->db->insert("products", $addData);
            }
            if ($insert) {
                $this->session->set_flashdata("product_add", "success");
                redirect(base_url("admin/products"));
            }

        } else {
            if ($this->input->post("mast_cat") == 0) {
                $this->session->set_flashdata("mastFail", "Ana Kategori Boş Olamaz");
            }
            $error = $this->form_validation->error_array();
            $this->session->set_flashdata("formError", $error);
            redirect(base_url("admin/product_add"));
        }
    }

public function edit_privacy_policy()
{
    // Dosya yolunu belirle
    $file_path = FCPATH . "assets/content/privacy_security.json";
    $json = file_get_contents($file_path);
    $data['json_data'] = $json;
    $data['privacy_list'] = json_decode($json, true);

    $this->load->view("admin/edit_privacy_policy", $data);
}

public function save_privacy_policy()
{
    $content = $this->input->post("content");
    $file_path = FCPATH . "assets/content/privacy_security.json";

    // Her itemda slug, view ve content olmalı!
    $json = json_encode($content, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

    file_put_contents($file_path, $json);

    $this->session->set_flashdata("success", "Gizlilik politikası başarıyla güncellendi!");
    redirect("admin/edit_privacy_policy");
}

public function GizlilikVeGuvenlilik() {
    $this->load->helper('tools_helper');
    $content = get_content_json("privacy_security");

    // BURASI KRİTİK!
    $slug = strtolower(current_lang());
    if(strlen($slug) > 2) $slug = substr($slug, 0, 2);

    // Şimdi eşleşme için anahtar dizisi hazırla
    $content_keys = [];
    foreach ($content as $index => $entry) {
        if (isset($entry['slug'])) {
            $content_keys[$entry['slug']] = $index;
        }
    }

    // İçeriği çek
    if (isset($content_keys[$slug])) {
        $lang_specific_content = $content[$content_keys[$slug]];
    } else {
        $lang_specific_content = $content[0]; // Varsayılan ilk dil
    }

	var_dump($slug);
var_dump(array_column($content, 'slug'));
exit;
    $data['content'] = $lang_specific_content['content'];

    $this->load->view('sozlesme/gizlilik', $data);
}

    public function product_image_delete($id= null){
        $this->db->where('id', $id);
        $this->db->delete('product_img');
        $url = htmlspecialchars($_SERVER['HTTP_REFERER']);
        header("Location: ".$url);
    }

    //excel
    public function excel_add()
    {
        $this->load->view("admin/excel-product/index");
    }

    public function excel_go()
    {
        $arr_file = explode('.', $_FILES['file']['name']);
        $extension = end($arr_file);
        if ('csv' == $extension) {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        } else {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        }
        $spreadsheet = $reader->load($_FILES['file']['tmp_name']);

        $sheetData = $spreadsheet->getActiveSheet()->toArray();
        if (!empty($sheetData)) {
            for ($i = 1; $i < count($sheetData); $i++) {
                $data['parentID'] =  strip_tags( $sheetData[$i][0]);
                $data['mast_cat_id'] = $sheetData[$i][1];
                $data['top_cat_id'] = $sheetData[$i][2];
                $data['sub_cat_id'] = $sheetData[$i][3];
                $data['bottom_cat_id'] = $sheetData[$i][4];
                $data['title'] = $sheetData[$i][5];
                $data['description'] = $sheetData[$i][6];
                $data['dimensions'] = $sheetData[$i][7];
                $data['slug'] = $sheetData[$i][8];
                $data['view'] = $sheetData[$i][9];
                $data['url'] = convertToSeo($sheetData[$i][5]);
                $data['createdAt'] = date("d-m-Y H:i:s");
                $data['status'] = 0;
                $insert = $this->db->insert("products", $data);
            }

            if ($insert) {
                $this->session->set_flashdata("product_add", "success");
                redirect(base_url("admin/products"));
            }
        }

    }

    public function product_update($id = null)
    {
        $product = $this->db->get_where("products", array("parentID" => $id))->result_array();
        if (!$id || empty($product)) {
            redirect(base_url("admin"));
        } else {
            $data['product'] = $product;
            $this->load->view("admin/products/update", $data);
        }
    }

    function product_update_go($id = null)
    {
        $updateDatas = $this->db->order_by("id", "ASC")->get_where("products", array("parentID" => $id))->result_array();
        $updateGroup = array($updateDatas[0]['id'], $updateDatas[1]['id'], $updateDatas[2]['id'], $updateDatas[3]['id']);
        if (!$id || empty($updateDatas)) {
            redirect(base_url("admin"));
        } else {
            $this->form_validation->set_rules("title[tr]", "Başlık TR", "trim|required");
            $this->form_validation->set_rules("title[en]", "Başlık EN", "trim|required");
            $this->form_validation->set_rules("title[ru]", "Başlık RU", "trim|required");
            $this->form_validation->set_rules("title[ar]", "Başlık Ar", "trim|required");
            $this->form_validation->set_rules("mast_cat", "Ana Kategori", "trim|required");
            $this->form_validation->set_rules("top_cat", "Üst Kategori", "trim|required");
            $this->form_validation->set_rules("sub_cat", "Alt Kategori", "trim|required");
            $this->form_validation->set_rules("bottom_cat", "En Alt Kategori", "trim|required");
            $this->form_validation->set_message(array("required" => "{field} Boş Olamaz"));
            $title = $this->input->post("title");
            $content = $this->input->post("content");
            $dimensions = $this->input->post("dimensions");

            $config["allowed_types"] = "jpg|jpeg|png|svg";
            $config["upload_path"] = "includes/images/product/";
            $this->load->library("upload", $config);
            $upload = $this->upload->do_upload("img");

            if ($this->form_validation->run()) {
                foreach (langs() as $langs) {
                    $this->db->where("parentID !=", $id);
                    $dbCheck = $this->db->get_where("products", array("url" => strip_tags(convertToSEO(strip_tags($title[$langs['slug']])))))->row_array();
                    if ($dbCheck) {
                        $this->session->set_flashdata("uniqueFailed", $dbCheck['parentID']);
                        redirect(base_url("admin/product_update/$id"));
                        exit();
                    }
                }
                foreach (langs() as $langs) {
                    ${"updateData" . $langs['slug']}['title'] = strip_tags($title[$langs['slug']]);
                    ${"updateData" . $langs['slug']}['description'] = $content[$langs['slug']];
                    ${"updateData" . $langs['slug']}['dimensions'] = $dimensions[$langs['slug']];
                    ${"updateData" . $langs['slug']}['url'] = convertToSeo(strip_tags($title[$langs['slug']]));
                    ${"updateData" . $langs['slug']}['mast_cat_id'] = strip_tags($this->input->post("mast_cat"));
                    ${"updateData" . $langs['slug']}['top_cat_id'] = strip_tags($this->input->post("top_cat"));
                    ${"updateData" . $langs['slug']}['sub_cat_id'] = strip_tags($this->input->post("sub_cat"));
                    ${"updateData" . $langs['slug']}['bottom_cat_id'] = strip_tags($this->input->post("bottom_cat"));
                    ${"updateData" . $langs['slug']}['status'] = strip_tags($this->input->post("status"));

                    if ($upload) {
                        $uploaded_file = $this->upload->data("file_name");
                        ${"updateData" . $langs['slug']}['img'] = $uploaded_file;
                    }
                }
                $this->db->where("id", $updateGroup[0]);
                $this->db->update("products", ${"updateData" . "tr"});
                $this->db->where("id", $updateGroup[1]);
                $this->db->update("products", ${"updateData" . "en"});
                $this->db->where("id", $updateGroup[2]);
                $this->db->update("products", ${"updateData" . "ru"});
                $this->db->where("id", $updateGroup[3]);
                $update = $this->db->update("products", ${"updateData" . "ar"});
                if ($update) {
                    $this->session->set_flashdata("product_update", "success");
                    redirect(base_url("admin/products"));
                }
            } else {
                $error = $this->form_validation->error_array();
                $this->session->set_flashdata("formError", $error);
                redirect(base_url("admin/product_update/" . $id));
            }
        }
    }

    public function excel_down(){
        $file=base_url("includes/images/logo/ornek.xlsx");
        header("Content-Disposition: attachment; filename = " . $file);
        readfile($file);
        exit();
    }

    public function product_img($id = null)
    {
        $product = $this->db->get_where("products", array("parentID" => $id))->result();
        if (!$id || empty($product)) {
            redirect(base_url("admin"));
        } else {
            $data['product'] = $product[0];
            $data['product_img'] = $this->db->get_where("product_img", array("productID" => $id))->result();
            $this->load->view("admin/products/product_img", $data);
        }
    }


    public function product_do_upload($id = null)
    {

        if (!empty($_FILES)) {
            $config["upload_path"] = "includes/images/product";
            $config["allowed_types"] = "*";
            $this->load->library('upload', $config);
            $upload = $this->upload->do_upload("file");
            $uploaded_file = $this->upload->data("file_name");
            if ($upload) {
                $addData['productID'] = $id;
                $addData['img'] = $uploaded_file;
                $addData['createdAt'] = date("d-m-Y H:i:s");
                $this->db->insert("product_img", $addData);
                $this->session->set_flashdata("productImgUpl", "success");
            } else {
                return Null;
            }
        }

    }

    public function product_options($parentID = null)
    {
        $this->db->where("productID", $parentID);
        $this->db->where("slug", "tr");
        $data['options'] = $this->db->get("product_options")->result();
        $data['parentID'] = $parentID;
        $this->load->view("admin/product-options/index", $data);
    }

    public function product_options_add($parentID = null)
    {
        $data['parentID'] = $parentID;
        $this->load->view("admin/product-options/add", $data);
    }

    public function product_options_add_go($parentID = null)
    {
        if (!$parentID || empty(masterPrd($parentID))) {
            redirect(base_url("admin"));
        } else {

            $this->session->set_flashdata("randKeyOne", random_string(20));
            $this->session->set_flashdata("randKeyTwo", random_string(20));
            $randKeyOne = $this->session->flashdata("randKeyOne");
            $randKeyTwo = $this->session->flashdata("randKeyTwo");
            $this->db->where("parentID", $randKeyOne);
            $randKeyQuery = $this->db->get("product_options")->row();
            $title = $this->input->post("title");

            $config["allowed_types"] = "jpg|jpeg|png|svg";
            $config["upload_path"] = "includes/images/product_options";
            $this->load->library("upload", $config);
            $upload = $this->upload->do_upload("img");
            $uploaded_file = $this->upload->data("file_name");

            foreach (langs() as $langs) {
                $addData['productID'] = $parentID;
                $addData['title'] = strip_tags($title[$langs['slug']]);
                $addData['slug'] = $langs['slug'];
                $addData['view'] = $langs['view'];
                $addData['img'] = $uploaded_file;
                $addData['status'] = strip_tags($this->input->post("status"));
                $addData['createdAt'] = date("d-m-Y H:i:s");
                if ($randKeyQuery) {
                    $addData['parentID'] = $randKeyTwo;
                } else {
                    $addData['parentID'] = $randKeyOne;
                }
                $insert = $this->db->insert("product_options", $addData);
            }
            if ($insert) {
                $this->session->set_flashdata("options_add", "success");
                redirect(base_url("admin/product_options/" . $parentID));
            }
        }
    }

    public function product_options_update($id = null)
    {
        $options = $this->db->get_where("product_options", array("parentID" => $id))->result_array();
        if (!$id || empty($options)) {
            redirect(base_url("admin"));
        } else {
            $data['options'] = $options;
            $this->db->where("parentID", $options[0]['productID']);
            $this->db->where("slug", "tr");
            $product = $this->db->get("products")->row();
            $data['product'] = $product;
            $this->load->view("admin/product-options/update", $data);
        }
    }

    public function home_banner_p()
    {
        $data['p_banner_home'] = $this->db->get("p_banner_home")->result_array();
        $this->load->view("admin/pages-m/banner_home", $data);
    }

    public function home_banner_p_update($id = null)
    {
        $updateDatas = $this->db->get_where("p_banner_home", array("parentID" => $id))->result_array();
        $updateGroup = array($updateDatas[0]['id'], $updateDatas[1]['id'], $updateDatas[2]['id'], $updateDatas[3]['id']);
        if (!$id || empty($updateDatas)) {
            redirect(base_url("admin"));
        } else {
            $this->form_validation->set_rules("content[tr]", "Metin TR", "trim|required");
            $this->form_validation->set_rules("content[en]", "Metin EN", "trim|required");
            $this->form_validation->set_rules("content[ru]", "Metin RU", "trim|required");
            $this->form_validation->set_rules("content[ar]", "Metin Ar", "trim|required");
            $this->form_validation->set_message(array("required" => "{field} Boş Olamaz"));

            $config["allowed_types"] = "jpg|jpeg|png|svg";
            $config["upload_path"] = "includes/images/pages_m";
            $this->load->library("upload", $config);
            $upload = $this->upload->do_upload("img");
            $content = $this->input->post("content");
            $title = $this->input->post("title");
            if ($this->form_validation->run()) {
                foreach (langs() as $langs) {
                    ${"updateData" . $langs['slug']}['text'] = $content[$langs['slug']];
                    ${"updateData" . $langs['slug']}['title'] = $title[$langs['slug']];
                    ${"updateData" . $langs['slug']}['status'] = $this->input->post("status");
                    if ($upload) {
                        $uploaded_file = $this->upload->data("file_name");
                        ${"updateData" . $langs['slug']}['img'] = $uploaded_file;
                    }
                }
                $this->db->where("id", $updateGroup[0]);
                $this->db->update("p_banner_home", ${"updateData" . "tr"});
                $this->db->where("id", $updateGroup[1]);
                $this->db->update("p_banner_home", ${"updateData" . "en"});
                $this->db->where("id", $updateGroup[2]);
                $this->db->update("p_banner_home", ${"updateData" . "ru"});
                $this->db->where("id", $updateGroup[3]);
                $update = $this->db->update("p_banner_home", ${"updateData" . "ar"});
                if ($update) {
                    $this->session->set_flashdata("banner_home", "success");
                    redirect(base_url("admin/home_banner_p"));
                }
            } else {
                $error = $this->form_validation->error_array();
                $this->session->set_flashdata("formError", $error);
                redirect(base_url("admin/home_banner_p/" . $id));
            }
        }
    }


    public function about_us_p()
    {
        $data['p_about_us'] = $this->db->get("p_about_us")->result_array();
        $this->load->view("admin/pages-m/about_us", $data);
    }

    public function about_us_p_update($id = null)
    {
        $updateDatas = $this->db->get_where("p_about_us", array("parentID" => $id))->result_array();
        $updateGroup = array($updateDatas[0]['id'], $updateDatas[1]['id'], $updateDatas[2]['id'], $updateDatas[3]['id']);
        if (!$id || empty($updateDatas)) {
            redirect(base_url("admin"));
        } else {
            $this->form_validation->set_rules("content[tr]", "Metin TR", "trim|required");
            $this->form_validation->set_rules("content[en]", "Metin EN", "trim|required");
            $this->form_validation->set_rules("content[ru]", "Metin RU", "trim|required");
            $this->form_validation->set_rules("content[ar]", "Metin Ar", "trim|required");
            $this->form_validation->set_message(array("required" => "{field} Boş Olamaz"));


            $config["allowed_types"] = "jpg|jpeg|png";
            $config["upload_path"] = "includes/images/pages_m/";
            $this->load->library("upload", $config);
            $upload_banner = $this->upload->do_upload("banner_img");


            $config["allowed_types"] = "jpg|jpeg|png|svg";
            $config["upload_path"] = "includes/images/pages_m";
            $this->load->library("upload", $config);
            $upload = $this->upload->do_upload("img");

            $content = $this->input->post("content");
            $title = $this->input->post("title");
            if ($this->form_validation->run()) {
                foreach (langs() as $langs) {
                    ${"updateData" . $langs['slug']}['text'] = $content[$langs['slug']];
                    if ($upload) {
                        $uploaded_file = $this->upload->data("file_name");
                        ${"updateData" . $langs['slug']}['img'] = $uploaded_file;
                    }

                    if ($upload_banner) {
                        $uploaded_file = $this->upload->data("file_name");
                        ${"updateData" . $langs['slug']}['banner_img'] = $uploaded_file;
                    }
                }
                $this->db->where("id", $updateGroup[0]);
                $this->db->update("p_about_us", ${"updateData" . "tr"});
                $this->db->where("id", $updateGroup[1]);
                $this->db->update("p_about_us", ${"updateData" . "en"});
                $this->db->where("id", $updateGroup[2]);
                $this->db->update("p_about_us", ${"updateData" . "ru"});
                $this->db->where("id", $updateGroup[3]);
                $update = $this->db->update("p_about_us", ${"updateData" . "ar"});
                if ($update) {
                    $this->session->set_flashdata("p_about_us", "success");
                    redirect(base_url("admin/about_us_p"));
                }
            } else {
                $error = $this->form_validation->error_array();
                $this->session->set_flashdata("formError", $error);
                redirect(base_url("admin/about_us_p/" . $id));
            }
        }
    }


    public function services_p()
    {
        $data['p_services'] = $this->db->get("p_services")->result_array();
        $this->load->view("admin/pages-m/services", $data);
    }

    public function services_p_update($id = null)
    {
        $updateDatas = $this->db->get_where("p_services", array("parentID" => $id))->result_array();
        $updateGroup = array($updateDatas[0]['id'], $updateDatas[1]['id'], $updateDatas[2]['id'], $updateDatas[3]['id']);
        if (!$id || empty($updateDatas)) {
            redirect(base_url("admin"));
        } else {
            $this->form_validation->set_rules("content[tr]", "Metin TR", "trim|required");
            $this->form_validation->set_rules("content[en]", "Metin EN", "trim|required");
            $this->form_validation->set_rules("content[ru]", "Metin RU", "trim|required");
            $this->form_validation->set_rules("content[ar]", "Metin Ar", "trim|required");
            $this->form_validation->set_message(array("required" => "{field} Boş Olamaz"));

            $config["allowed_types"] = "jpg|jpeg|png|svg";
            $config["upload_path"] = "includes/images/pages_m/";
            $this->load->library("upload", $config);
            $upload = $this->upload->do_upload("img");
            $content = $this->input->post("content");

            if ($this->form_validation->run()) {
                foreach (langs() as $langs) {
                    ${"updateData" . $langs['slug']}['text'] = $content[$langs['slug']];
                    if ($upload) {
                        $uploaded_file = $this->upload->data("file_name");
                        ${"updateData" . $langs['slug']}['img'] = $uploaded_file;
                    }
                }
                $this->db->where("id", $updateGroup[0]);
                $this->db->update("p_services", ${"updateData" . "tr"});
                $this->db->where("id", $updateGroup[1]);
                $this->db->update("p_services", ${"updateData" . "en"});
                $this->db->where("id", $updateGroup[2]);
                $this->db->update("p_services", ${"updateData" . "ru"});
                $this->db->where("id", $updateGroup[3]);
                $update = $this->db->update("p_services", ${"updateData" . "ar"});
                if ($update) {
                    $this->session->set_flashdata("services_update", "success");
                    redirect(base_url("admin/services_p"));
                }
            } else {
                $error = $this->form_validation->error_array();
                $this->session->set_flashdata("formError", $error);
                redirect(base_url("admin/services_p/" . $id));
            }
        }
    }

    public function upload_img_check($id = null)
    {
        $resim = $this->input->post("field-avatar");
        if ($resim) {
            $addData['img'] = implode(',', $this->input->post('field-avatar[]'));
        } else {
            $addData['img'] = null;
        }
        var_dump($addData['img']);
    }


    public function services_page()
    {
        $data['services'] = $this->db->get_where("services", array("slug" => "tr"))->result();
        $this->load->view("admin/services/index", $data);
    }


    public function service_add()
    {
        $this->load->view("admin/services/add");
    }

    public function service_add_go()
    {
        $this->form_validation->set_rules("title[tr]", "Başlık TR", "trim|required");
        $this->form_validation->set_rules("title[en]", "Başlık EN", "trim|required");
        $this->form_validation->set_rules("title[ru]", "Başlık RU", "trim|required");
        $this->form_validation->set_rules("title[ar]", "Başlık AR", "trim|required");
        $this->form_validation->set_message(array("required" => "{field} Boş Olamaz"));
        if ($this->form_validation->run()) {
            $this->session->set_flashdata("randKeyOne", random_string(20));
            $this->session->set_flashdata("randKeyTwo", random_string(20));
            $randKeyOne = $this->session->flashdata("randKeyOne");
            $randKeyTwo = $this->session->flashdata("randKeyTwo");
            $this->db->where("parentID", $randKeyOne);
            $randKeyQuery = $this->db->get("services")->row();
            $title = $this->input->post("title");
            $content = $this->input->post("content");

            $config["allowed_types"] = "jpg|jpeg|png|svg";
            $config["upload_path"] = "includes/images/services";
            $this->load->library("upload", $config);
            $upload = $this->upload->do_upload("img");
            $uploaded_file = $this->upload->data("file_name");

            foreach (langs() as $langs) {
                $dbCheck = $this->db->get_where("services", array("url" => strip_tags(convertToSEO(strip_tags($title[$langs['slug']])))))->row_array();
                if ($dbCheck) {
                    $this->session->set_flashdata("uniqueFailed", $dbCheck['id']);
                    redirect(base_url("admin/service_add"));
                    exit();
                }
            }
            foreach (langs() as $langs) {
                $addData['title'] = strip_tags($title[$langs['slug']]);
                $addData['description'] = strip_tags($content[$langs['slug']]);
                $addData['slug'] = $langs['slug'];
                $addData['view'] = $langs['view'];
                $addData['img'] = $uploaded_file;
                $addData['url'] = convertToSeo(strip_tags($title[$langs['slug']]));
                $addData['status'] = strip_tags($this->input->post("status"));
                $addData['createdAt'] = date("d-m-Y H:i:s");
                if ($randKeyQuery) {
                    $addData['parentID'] = $randKeyTwo;
                } else {
                    $addData['parentID'] = $randKeyOne;
                }
                $insert = $this->db->insert("services", $addData);
            }
            if ($insert) {
                $this->session->set_flashdata("service_add", "success");
                redirect(base_url("admin/services_page"));
            }

        } else {
            $error = $this->form_validation->error_array();
            $this->session->set_flashdata("formError", $error);
            redirect(base_url("admin/service_add"));
        }
    }

    public function service_update($id = null)
    {
        $service = $this->db->get_where("services", array("parentID" => $id))->result_array();
        if (!$id || empty($service)) {
            redirect(base_url("admin"));
        } else {
            $data['service'] = $service;
            $this->load->view("admin/services/update", $data);
        }
    }

    function service_update_go($id = null)
    {
        $updateDatas = $this->db->get_where("services", array("parentID" => $id))->result_array();
        $updateGroup = array($updateDatas[0]['id'], $updateDatas[1]['id'], $updateDatas[2]['id'], $updateDatas[3]['id']);
        if (!$id || empty($updateDatas)) {
            redirect(base_url("admin"));
        } else {
            $this->form_validation->set_rules("title[tr]", "Başlık TR", "trim|required");
            $this->form_validation->set_rules("title[en]", "Başlık EN", "trim|required");
            $this->form_validation->set_rules("title[ru]", "Başlık RU", "trim|required");
            $this->form_validation->set_rules("title[ar]", "Başlık Ar", "trim|required");
            $this->form_validation->set_message(array("required" => "{field} Boş Olamaz"));
            $title = $this->input->post("title");
            $content = $this->input->post("content");

            $config["allowed_types"] = "jpg|jpeg|png|svg";
            $config["upload_path"] = "includes/images/services/";
            $this->load->library("upload", $config);
            $upload = $this->upload->do_upload("img");

            if ($this->form_validation->run()) {
                foreach (langs() as $langs) {
                    $this->db->where("parentID !=", $id);
                    $dbCheck = $this->db->get_where("services", array("url" => strip_tags(convertToSEO(strip_tags($title[$langs['slug']])))))->row_array();
                    if ($dbCheck) {
                        $this->session->set_flashdata("uniqueFailed", $dbCheck['parentID']);
                        redirect(base_url("admin/service_update/$id"));
                        exit();
                    }
                }

                foreach (langs() as $langs) {
                    ${"updateData" . $langs['slug']}['title'] = strip_tags($title[$langs['slug']]);
                    ${"updateData" . $langs['slug']}['description'] = $content[$langs['slug']];
                    ${"updateData" . $langs['slug']}['url'] = convertToSeo(strip_tags($title[$langs['slug']]));
                    ${"updateData" . $langs['slug']}['status'] = strip_tags($this->input->post("status"));

                    if ($upload) {
                        $uploaded_file = $this->upload->data("file_name");
                        ${"updateData" . $langs['slug']}['img'] = $uploaded_file;
                    }
                }
                $this->db->where("id", $updateGroup[0]);
                $this->db->update("services", ${"updateData" . "tr"});
                $this->db->where("id", $updateGroup[1]);
                $this->db->update("services", ${"updateData" . "en"});
                $this->db->where("id", $updateGroup[2]);
                $this->db->update("services", ${"updateData" . "ru"});
                $this->db->where("id", $updateGroup[3]);
                $update = $this->db->update("services", ${"updateData" . "ar"});
                if ($update) {
                    $this->session->set_flashdata("service_update", "success");
                    redirect(base_url("admin/services_page"));
                }
            } else {
                // $error = $this->form_validation->error_array();
                // $this->session->set_flashdata("formError", $error);
                //redirect(base_url("admin/service_update/" . $id));
                print_r($this->form_validation->error_array());
                print_r($this->upload->display_errors());
            }
        }
    }


    public function service_delete($id = "")
    {
        $deletedData = $this->services_model->get(array("parentID" => $id));
        if (!$id || empty($deletedData)) {
            redirect(base_url("admin"));
        } else {
            $delete = $this->db->delete("services", array("parentID" => $id));
            if ($delete) {
                $this->session->set_flashdata("service_delete", "success");
                redirect(base_url("admin/services_page"));
            }
        }
    }

    public function blog()
    {
        $data['blog'] = $this->db->get_where("blog", array("slug" => "tr"))->result();
        $this->load->view("admin/blog/index", $data);
    }

    public function blog_add()
    {
        $this->load->view("admin/blog/add");
    }

    public function blog_add_go()
    {
        $this->form_validation->set_rules("title[tr]", "Başlık TR", "trim|required");
        $this->form_validation->set_rules("title[en]", "Başlık EN", "trim|required");
        $this->form_validation->set_rules("title[ru]", "Başlık RU", "trim|required");
        $this->form_validation->set_rules("title[ar]", "Başlık AR", "trim|required");
        $this->form_validation->set_message(array("required" => "{field} Boş Olamaz"));
        if ($this->form_validation->run()) {
            $this->session->set_flashdata("randKeyOne", random_string(20));
            $this->session->set_flashdata("randKeyTwo", random_string(20));
            $randKeyOne = $this->session->flashdata("randKeyOne");
            $randKeyTwo = $this->session->flashdata("randKeyTwo");
            $this->db->where("parentID", $randKeyOne);
            $randKeyQuery = $this->db->get("blog")->row();
            $title = $this->input->post("title");
            $content = $this->input->post("content");

            $config["allowed_types"] = "jpg|jpeg|png|svg";
            $config["upload_path"] = "includes/images/blog";
            $this->load->library("upload", $config);
            $upload = $this->upload->do_upload("img");
            $uploaded_file = $this->upload->data("file_name");

            foreach (langs() as $langs) {
                $dbCheck = $this->db->get_where("blog", array("url" => strip_tags(convertToSEO(strip_tags($title[$langs['slug']])))))->row_array();
                if ($dbCheck) {
                    $this->session->set_flashdata("uniqueFailed", $dbCheck['id']);
                    redirect(base_url("admin/blog_add"));
                    exit();
                }
            }
            foreach (langs() as $langs) {
                $addData['title'] = strip_tags($title[$langs['slug']]);
                $addData['description'] = strip_tags($content[$langs['slug']]);
                $addData['slug'] = $langs['slug'];
                $addData['view'] = $langs['view'];
                $addData['img'] = $uploaded_file;
                $addData['url'] = convertToSeo(strip_tags($title[$langs['slug']]));
                $addData['status'] = strip_tags($this->input->post("status"));
                $addData['createdAt'] = date("d-m-Y H:i:s");
                if ($randKeyQuery) {
                    $addData['parentID'] = $randKeyTwo;
                } else {
                    $addData['parentID'] = $randKeyOne;
                }
                $insert = $this->db->insert("blog", $addData);
            }
            if ($insert) {
                $this->session->set_flashdata("blog_add", "success");
                redirect(base_url("admin/blog"));
            }

        } else {
            $error = $this->form_validation->error_array();
            $this->session->set_flashdata("formError", $error);
            redirect(base_url("admin/blog_add"));
        }
    }

    public function blog_update($id = null)
    {
        $blog = $this->db->get_where("blog", array("parentID" => $id))->result_array();
        if (!$id || empty($blog)) {
            redirect(base_url("admin"));
        } else {
            $data['blog'] = $blog;
            $this->load->view("admin/blog/update", $data);
        }
    }

    function blog_update_go($id = null)
    {
        $updateDatas = $this->db->get_where("blog", array("parentID" => $id))->result_array();
        $updateGroup = array($updateDatas[0]['id'], $updateDatas[1]['id'], $updateDatas[2]['id'], $updateDatas[3]['id']);
        if (!$id || empty($updateDatas)) {
            redirect(base_url("admin"));
        } else {
            $this->form_validation->set_rules("title[tr]", "Başlık TR", "trim|required");
            $this->form_validation->set_rules("title[en]", "Başlık EN", "trim|required");
            $this->form_validation->set_rules("title[ru]", "Başlık RU", "trim|required");
            $this->form_validation->set_rules("title[ar]", "Başlık Ar", "trim|required");
            $this->form_validation->set_message(array("required" => "{field} Boş Olamaz"));
            $title = $this->input->post("title");
            $content = $this->input->post("content");

            $config["allowed_types"] = "jpg|jpeg|png|svg";
            $config["upload_path"] = "includes/images/blog/";
            $this->load->library("upload", $config);
            $upload = $this->upload->do_upload("img");

            if ($this->form_validation->run()) {
                foreach (langs() as $langs) {
                    $this->db->where("parentID !=", $id);
                    $dbCheck = $this->db->get_where("blog", array("url" => strip_tags(convertToSEO(strip_tags($title[$langs['slug']])))))->row_array();
                    if ($dbCheck) {
                        $this->session->set_flashdata("uniqueFailed", $dbCheck['parentID']);
                        redirect(base_url("admin/blog_update/$id"));
                        exit();
                    }
                }
                foreach (langs() as $langs) {
                    ${"updateData" . $langs['slug']}['title'] = strip_tags($title[$langs['slug']]);
                    ${"updateData" . $langs['slug']}['description'] = $content[$langs['slug']];
                    ${"updateData" . $langs['slug']}['url'] = convertToSeo(strip_tags($title[$langs['slug']]));
                    ${"updateData" . $langs['slug']}['status'] = strip_tags($this->input->post("status"));

                    if ($upload) {
                        $uploaded_file = $this->upload->data("file_name");
                        ${"updateData" . $langs['slug']}['img'] = $uploaded_file;
                    }
                }
                $this->db->where("id", $updateGroup[0]);
                $this->db->update("blog", ${"updateData" . "tr"});
                $this->db->where("id", $updateGroup[1]);
                $this->db->update("blog", ${"updateData" . "en"});
                $this->db->where("id", $updateGroup[2]);
                $this->db->update("blog", ${"updateData" . "ru"});
                $this->db->where("id", $updateGroup[3]);
                $update = $this->db->update("blog", ${"updateData" . "ar"});
                if ($update) {
                    $this->session->set_flashdata("blog_update", "success");
                    redirect(base_url("admin/blog"));
                }
            } else {
                $error = $this->form_validation->error_array();
                $this->session->set_flashdata("formError", $error);
                redirect(base_url("admin/blog_update/" . $id));
            }
        }
    }

    function blog_delete($id = null){
        $this->db->where('parentID', $id);
        $this->db->delete('blog');
        $url = htmlspecialchars($_SERVER['HTTP_REFERER']);
        header("Location: ".$url);
    }

    public function pages()
    {
        if (isset($_GET['page'])) {
            $data['pages'] = $this->pages_model->get_all(
                array("slug" => $_GET['page']));
        } else {
            redirect(base_url("admin"));
        }

        $this->load->view("admin/pages/index", $data);
    }

    public function pages_add()
    {
        $slug = $_GET['add'];
        $data['slug'] = $slug;
        $this->load->view("admin/pages/add", $data);
    }

    public function pages_add_go($name = "")
    {
        if (isset($_GET['add']) || $name) {
            $config["allowed_types"] = "jpg|jpeg|png";
            $config["upload_path"] = "includes/images/pages/";
            $this->load->library("upload", $config);
            $upload = $this->upload->do_upload("img");
            $uploaded_file = $this->upload->data("file_name");
            $insert = $this->pages_model->add(
                array(
                    "name" => strip_tags($name),
                    "title" => strip_tags($this->input->post("title")),
                    "img" => $uploaded_file,
                    "description" => strip_tags($this->input->post("description")),
                    "text" => $this->input->post("text"),
                    "url" => strip_tags(convertToSEO($this->input->post("title"))),
                    "slug" => strip_tags($_GET['add']),
                    "status" => strip_tags($this->input->post("status")),
                    "createdAt" => date("d-m-Y H:i:s")
                )
            );
            $get = $_GET['add'];
            if ($insert) {
                $this->session->set_flashdata("pages_add", "success");
                redirect(base_url("admin/pages/?page=") . $get);
            }
        } else {
            redirect(base_url("admin"));
        }
    }

    public function pages_update($id = "")
    {
        $pages = $this->pages_model->get(array("id" => $id));
        if (!$id || empty($pages)) {
            redirect(base_url("admin"));
        } else {
            $data['pages'] = $pages;
            $this->load->view("admin/pages/update", $data);
        }
    }

    public function pages_update_go($id = "")
    {
        $updatedData = $this->pages_model->get(array("id" => $id));
        if (!$id || empty($updatedData)) {
            redirect(base_url("admin"));
        } else {
            $config["allowed_types"] = "jpg|jpeg|png";
            $config["upload_path"] = "includes/images/pages/";
            $this->load->library("upload", $config);
            $upload = $this->upload->do_upload("img");
            if ($upload) {
                $uploaded_file = $this->upload->data("file_name");
                $this->pages_model->uptade(
                    array("id" => $id),
                    array("img" => $uploaded_file)
                );
            }
            $update = $this->pages_model->uptade(
                array("id" => $id),
                array(
                    "title" => strip_tags($this->input->post("title")),
                    "description" => strip_tags($this->input->post("description")),
                    "text" => $this->input->post("text"),
                    "url" => strip_tags(convertToSEO($this->input->post("title"))),
                    "status" => strip_tags($this->input->post("status")),
                )
            );
            $slug = $this->pages_model->get(array("id" => $id)
            );
            if ($upload || $update) {
                $this->session->set_flashdata("pages_update", "success");
                redirect(base_url("admin/pages/?page=") . $slug->slug);
            }
        }
    }

    public function pages_delete($id = "")
    {
        $slug = $this->pages_model->get(array("id" => $id));
        if (!$id || empty($slug)) {
            redirect(base_url("admin"));
        } else {
            $delete = $this->pages_model->delete(array("id" => $id));
            if ($delete) {
                $this->session->set_flashdata("pages_delete", "success");
                redirect(base_url("admin/pages/?page=") . $slug->slug);
            }
        }
    }

    public function references()
    {
        $data['references'] = $this->references_model->get_all();
        $this->load->view("admin/references/index", $data);
    }

    public function reference_add()
    {
        $this->load->view("admin/references/add");
    }

    public function reference_add_go()
    {
        $config["allowed_types"] = "jpg|jpeg|png";
        $config["upload_path"] = "includes/images/references";

        $this->load->library("upload", $config);
        $upload = $this->upload->do_upload("img");
        $uploaded_file = $this->upload->data("file_name");
        $insert = $this->references_model->add(
            array(
                "img" => $uploaded_file,
                "status" => strip_tags($this->input->post("status")),
                "createdAt" => date("d-m-Y H:i:s")
            )
        );

        if ($insert) {
            $this->session->set_flashdata("reference_add", "success");
            redirect(base_url("admin/references"));
        }
    }

    public function reference_update($id = null)
    {
        $reference = $this->references_model->get(array("id" => $id));
        if (!$id || empty($reference)) {
            redirect(base_url("admin/references"));

        } else {
            $data['reference'] = $reference;
            $this->load->view("admin/references/update", $data);
        }
    }

    public function reference_update_go($id = null)
    {

        $updatedData = $this->references_model->get(array("id" => $id));
        if (!$id || empty($updatedData)) {
            redirect(base_url("admin"));
        } else {
            $config["allowed_types"] = "jpg|jpeg|png";
            $config["upload_path"] = "includes/images/references/";

            $this->load->library("upload", $config);
            $upload = $this->upload->do_upload("img");
            if ($upload) {
                $uploaded_file = $this->upload->data("file_name");
                $this->references_model->uptade(
                    array("id" => $id),
                    array("img" => $uploaded_file)
                );
            }
            $update = $this->references_model->uptade(
                array("id" => $id),
                array(
                    "status" => strip_tags($this->input->post("status")),
                    "createdAt" => date("d-m-Y H:i:s")
                )
            );
            if ($update || $upload) {
                $this->session->set_flashdata("reference_update", "success");
                redirect(base_url("admin/references"));
            }
        }
    }

    public function reference_delete($id = null)
    {
        $deletedData = $this->references_model->get(array("id" => $id));
        if (!$id || empty($deletedData)) {
            redirect(base_url("admin/references"));
        } else {
            $delete = $this->references_model->delete(array("id" => $id));
            if ($delete) {
                $this->session->set_flashdata("reference_delete", "success");
                redirect(base_url("admin/references"));
            }

        }
    }

    public function messages()
    {
        $this->load->library("pagination");
        $config = array();
        $config["reuse_query_string"] = true;
        $config["base_url"] = base_url("admin/messages");
        $config["total_rows"] = $this->db->get_where("messages")->num_rows();
        $config["per_page"] = 20;
        $config["uri_segment"] = 3;
        $config['full_tag_open'] = ' <ul class="pagination"> ';
        $config['full_tag_close'] = ' </ul> ';
        $config['attributes'] = ['class' => 'page-link'];
        $config['first_link'] = false;
        $config['last_link'] = false;
        $config['first_tag_open'] = ' <li class="page-item"> ';
        $config['first_tag_close'] = ' </li> ';
        $config['prev_link'] = ' &laquo';
        $config['prev_tag_open'] = ' <li class="page-item"> ';
        $config['prev_tag_close'] = ' </li> ';
        $config['next_link'] = ' &raquo';
        $config['next_tag_open'] = ' <li class="page-item"> ';
        $config['next_tag_close'] = ' </li> ';
        $config['last_tag_open'] = ' <li class="page-item"> ';
        $config['last_tag_close'] = ' </li> ';
        $config['cur_tag_open'] = ' <li class="page-item active"><a href = "#" class="page-link"> ';
        $config['cur_tag_close'] = ' <span class="sr-only"> (current)</span></a></li> ';
        $config['num_tag_open'] = ' <li class="page-item"> ';
        $config['num_tag_close'] = ' </li> ';
        $this->pagination->initialize($config);
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $data['startcount'] = $this->uri->segment(3);
        $data["links"] = $this->pagination->create_links();
        $data['messages'] = $this->db->limit($config["per_page"], $page)->order_by("id", "DESC")->get("messages")->result();

        //---Pagination End------

        $this->load->view("admin/messages/index", $data);
    }

    function message_detail($id = null)
    {
        $message = $this->db->get_where("messages", array("id" => $id))->row();
        if (!$id || empty($message)) {
            redirect(base_url("admin"));
        } else {
            if ($message->status == 0) {
                $updtData['status'] = 1;
                $this->db->where("id", $message->id);
                $this->db->update("messages", $updtData);
            }
            $data['message'] = $message;
            $this->load->view("admin/messages/detail", $data);
        }
    }

    function message_delete($id = null)
    {
        $deletedData = $this->db->get_where("messages", array("id" => $id))->row();
        if (!$id || empty($deletedData)) {
            redirect(base_url("admin"));
        } else {
            $delete = $this->db->delete("messages", array("id" => $id));
            if ($delete) {
                $this->session->set_flashdata("message_delete", "success");
                redirect(base_url("admin/messages"));
            }
        }
    }


    public function orders()
    {
        $this->load->library("pagination");
        $config = array();
        $config["reuse_query_string"] = true;
        $config["base_url"] = base_url("admin/orders");
        $config["total_rows"] = $this->db->get_where("orders")->num_rows();
        $config["per_page"] = 20;
        $config["uri_segment"] = 3;
        $config['full_tag_open'] = ' <ul class="pagination"> ';
        $config['full_tag_close'] = ' </ul> ';
        $config['attributes'] = ['class' => 'page-link'];
        $config['first_link'] = false;
        $config['last_link'] = false;
        $config['first_tag_open'] = ' <li class="page-item"> ';
        $config['first_tag_close'] = ' </li> ';
        $config['prev_link'] = ' &laquo';
        $config['prev_tag_open'] = ' <li class="page-item"> ';
        $config['prev_tag_close'] = ' </li> ';
        $config['next_link'] = ' &raquo';
        $config['next_tag_open'] = ' <li class="page-item"> ';
        $config['next_tag_close'] = ' </li> ';
        $config['last_tag_open'] = ' <li class="page-item"> ';
        $config['last_tag_close'] = ' </li> ';
        $config['cur_tag_open'] = ' <li class="page-item active"><a href = "#" class="page-link"> ';
        $config['cur_tag_close'] = ' <span class="sr-only"> (current)</span></a></li> ';
        $config['num_tag_open'] = ' <li class="page-item"> ';
        $config['num_tag_close'] = ' </li> ';
        $this->pagination->initialize($config);
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $data['startcount'] = $this->uri->segment(3);
        $data["links"] = $this->pagination->create_links();
        $data['orders'] = $this->db->limit($config["per_page"], $page)->order_by("id", "DESC")->get("orders")->result();

        //---Pagination End------

        $this->load->view("admin/orders/index", $data);
    }

    function order_detail($id = null)
    {
        $order = $this->db->get_where("orders", array("id" => $id))->row();
        if (!$id || empty($order)) {
            redirect(base_url("admin"));
        } else {
            if (!empty($order->options)) {
                $option = $this->db->get_where("product_options", array("parentID" => $order->options, "slug" => "tr"))->row();
                $data['option'] = $option;
            } else {
                $option = NULL;
                $data['option'] = NULL;
            }

            $data['order'] = $order;

            $this->load->view("admin/orders/detail", $data);
        }
    }

    function order_update($id = null)
    {
        //status(0) = Yeni
        //status(1) = Görüldü
        //status(3) = Tamamlandı
        $updatedData = $this->db->get_where("orders", array("id" => $id))->row();
        if (!$id || empty($updatedData) || !$this->input->post("status")) {
            redirect(base_url("admin"));
        } else {
            $updtData['status'] = strip_tags($this->input->post("status"));
            $this->db->where("id", $id);
            $update = $this->db->update("orders", $updtData);
            if ($update) {
                $this->session->set_flashdata("order_update", $this->input->post("status"));
                redirect(base_url("admin/orders"));
            }
        }
    }

    function order_delete($id = null)
    {
        $deletedData = $this->db->get_where("orders", array("id" => $id))->row();
        if (!$id || empty($deletedData)) {
            redirect(base_url("admin"));
        } else {
            $delete = $this->db->delete("orders", array("id" => $id));
            if ($delete) {
                $this->session->set_flashdata("order_delete", "success");
                redirect(base_url("admin/orders"));
            }
        }
    }


    public function getTopCat()
    {
        if ($this->input->post("id")) {
            $parentID = $this->input->post("id");
            $this->db->where("slug", "tr");
            $query = $this->db->get_where("category", array("parentCat" => $parentID, "cat_status" => 0))->result();
            if ($query) {
                foreach ($query as $que) {
                    $response .= "<option value='" . $que->parentID . "'>" . $que->title . "</option>";
                    //echo $response;
                }
                echo "<option value=''>Kategori Seçiniz</option>" . $response;

            } elseif (!$query || $parentID == '') {
                $response = "<option value=''>Üst Kategori Yok</option>";
                echo $response;
            }
        }
    }


    public function getSubCats()
     {
        if ($this->input->post("id")) {
          $parentID = $this->input->post("id");
        $this->db->where("slug", "tr");
         $query = $this->db->get_where("category", array("parentTopCat" => $parentID, "cat_status" => 2))->result();
         if ($query) {
            foreach ($query as $que) {
                 $response .= "<option value='" . $que->parentID . "'>" . $que->title . "</option>";
                 //echo $response;
           }
        echo "<option value=''>Kategori Seçiniz</option>" . $response;

        } elseif (!$query || $parentID == '') {
            $response = "<option value=''>Alt Kategori Yok</option>";
            echo $response;
       }
      }
      }

    public function getSubCat()
    {
        if ($this->input->post("id")) {
            $parentID = $this->input->post("id");
            $this->db->where("slug", "tr");
            $query = $this->db->get_where("category", array("parentTopCat" => $parentID, "cat_status" => 2))->result();
            if ($query) {
                foreach ($query as $que) {
                    $response .= "<option value='" . $que->parentID . "'>" . $que->title . "</option>";
                }
                echo "<option value=''>Kategori Seçiniz</option>" . $response;

            } elseif (!$query || $parentID == 0) {
                $response = "<option value=''>Alt Kategori Yok</option>";
                echo $response;
            }
        }
    }


    public function getBottomCats()
    {
        if ($this->input->post("id")) {
            $parentID = $this->input->post("id");
            $this->db->where("slug", "tr");
            $query = $this->db->get_where("category", array("parentSubCat" => $parentID, "cat_status" => 3))->result();
            if ($query) {
                foreach ($query as $que) {
                    $response .= "<option value='" . $que->parentID . "'>" . $que->title . "</option>";
                }
                echo "<option value=''>Kategori Seçiniz</option>" . $response;

            } elseif (!$query || $parentID == 0) {
                $response = "<option value=''>En Alt Kategori Yok</option>";
                echo $response;
            }
        }
    }

    public function getTopUpdtCat()
    {
        if ($this->input->post("mastid") && $this->input->post("topid")) {
            $mastID = $this->input->post("mastid");
            $topID = $this->input->post("topid");
            $this->db->where("slug", "tr");
            $query = $this->db->get_where("category", array("parentCat" => $mastID, "cat_status" => 0))->result();
            $selected = "selected";
            if ($query) {
                foreach ($query as $que) {
                    $response = "<option  " . ($que->parentID == $this->input->post('topid') ? $selected : null) . "  value='" . $que->parentID . "'> " . $que->title . "</option>";
                    echo $response;
                }
            } elseif (!$query || $mastID == 0) {
                $response = "<option value=''>Alt Kategori Yok</option>";
                echo $response;
            }
        }
    }

    public function getSubUpdtCat()
    {
        if ($this->input->post("mastid") && $this->input->post("subid") && $this->input->post("topid")) {
            $mastID = $this->input->post("mastid");
            $topID = $this->input->post("topid");
            $subID = $this->input->post("subid");
            $this->db->where("slug", "tr");
            $query = $this->db->get_where("category", array("parentCat" => $mastID, "parentTopCat" => $topID, "cat_status" => 2))->result();
            $selected = "selected";
            if ($query) {
                foreach ($query as $que) {
                    $response = "<option  " . ($que->parentID == $this->input->post('subid') ? $selected : null) . "  value='" . $que->parentID . "'> " . $que->title . "</option>";
                    echo $response;
                }
            } elseif (!$query || $mastID == 0) {
                $response = "<option value=''>Alt Kategori Yok</option>";
                echo $response;
            }
        }
    }

    public function getBottomUpdtCat()
    {
        if ($this->input->post("mastid") && $this->input->post("subid") && $this->input->post("topid")) {
            $mastID = $this->input->post("mastid");
            $topID = $this->input->post("topid");
            $subID = $this->input->post("subid");
            $bottomID = $this->input->post("bottomid");
            $this->db->where("slug", "tr");
            $query = $this->db->get_where("category", array("parentCat" => $mastID, "parentTopCat" => $topID, "parentSubCat" => $subID, "cat_status" => 3))->result();
            $selected = "selected";
            if ($query) {
                foreach ($query as $que) {
                    $response = "<option  " . ($que->parentID == $this->input->post('subid') ? $selected : null) . "  value='" . $que->parentID . "'> " . $que->title . "</option>";
                    echo $response;
                }
            } elseif (!$query || $mastID == 0) {
                $response = "<option value=''>Alt Kategori Yok</option>";
                echo $response;
            }
        }

    }

    public function checkboxAction($table, $action)
    {
        if (!empty($this->input->post("select"))) {
            $count = count($this->input->post("select"));
            $slug = $this->$table->get(
                array("id" => $this->input->post("select")[0])
            );
            if ($action == "active") {
                for ($i = 0; $i < $count; $i++) {
                    $updateActive = $this->$table->uptade(
                        array("id" => $this->input->post("select")[$i]),
                        array("status" => 1)
                    );
                }
                if ($updateActive) {
                    $this->session->set_flashdata("multi_upd", "success");
                    redirect(base_url("admin/pages/?page=") . $slug->slug);
                }
            } elseif ($action == "passive") {
                for ($i = 0; $i < $count; $i++) {
                    $updatePassive = $this->$table->uptade(
                        array("id" => $this->input->post("select")[$i]),
                        array("status" => 0)
                    );
                }
                if ($updatePassive) {
                    $this->session->set_flashdata("multi_upd", "success");
                    redirect(base_url("admin/pages/?page=") . $slug->slug);
                }
            } elseif ($action == "delete") {
                $count = count($this->input->post("select"));
                for ($i = 0; $i < $count; $i++) {
                    $delete = $this->$table->delete(
                        array("id" => $this->input->post("select")[$i])
                    );
                }
                if ($delete) {
                    $this->session->set_flashdata("multi_del", "success");
                    redirect(base_url("admin/pages/?page=") . $slug->slug);
                }
            } else {
                redirect(base_url("admin"));
            }
        } else {
            redirect(base_url("admin"));
        }
    }

    public function logout()
    {
        $this->session->unset_userdata("login");
        redirect(base_url("adminlogin"));
    }
    public function deneme(){
        $a = data_getir("select * from category where parentTopCat = 'aMPzMyhutlz6D0J9evlO' and cat_status =2 ");
        echo '<pre>';
        print_r($a);
        echo '</pre>';
}

    // --- Popup Management Functions ---
    public function popups()
    {
        // Popup_model zaten __construct'ta yüklendiği için tekrar yüklemeye gerek yok
        $data["popups"] = $this->Popup_model->get_all();
        $this->load->view("admin/popups/index", $data);
    }

    public function add_popup()
    {
        $this->load->view("admin/popups/add");
    }

    public function save_popup()
    {
        // Form doğrulama kurallarını tanımla
        $this->form_validation->set_rules('title', 'Başlık', 'trim|required');
        $this->form_validation->set_rules('start_date', 'Başlangıç Tarihi', 'required');
        $this->form_validation->set_rules('end_date', 'Bitiş Tarihi', 'required');

        // Hata mesajlarını ayarla
        $this->form_validation->set_message('required', '{field} alanı boş bırakılamaz.');

        if ($this->form_validation->run() == FALSE) {
            // Form doğrulama başarısız olursa
            $this->session->set_flashdata("formError", true);
            $this->session->set_flashdata("title_error", form_error('title'));
            $this->session->set_flashdata("start_date_error", form_error('start_date'));
            $this->session->set_flashdata("end_date_error", form_error('end_date'));
            redirect(base_url("admin/add_popup"));
        } else {
            // Görsel yükleme yapılandırması
            $config['upload_path']   = './uploads/popups/';
            $config['allowed_types'] = 'jpg|png|jpeg|gif';
            $config['overwrite']     = FALSE; // Varolan dosyayı overwrite etme
            $this->load->library('upload', $config);

            $img = "";
            if ($this->upload->do_upload('image')) {
                $data = $this->upload->data();
                $img = $data['file_name'];
            } else {
                // Görsel yükleme hatası olursa
                $this->session->set_flashdata("formError", true);
                $this->session->set_flashdata("image_error", $this->upload->display_errors());
                redirect(base_url("admin/add_popup"));
                return; // Hata durumunda fonksiyonu sonlandır
            }

            $insert_data = [
                "title"      => $this->input->post("title"),
                "image"      => $img,
                "start_date" => $this->input->post("start_date"),
                "end_date"   => $this->input->post("end_date"),
                "status"     => $this->input->post("status") ? 1 : 0, // Statüsü de formdan al
            ];

            if ($this->Popup_model->insert($insert_data)) {
                $this->session->set_flashdata("popup_add_success", true);
                redirect("admin/popups");
            } else {
                // Veritabanına kaydetme başarısız olursa
                $this->session->set_flashdata("formError", true);
                redirect(base_url("admin/add_popup"));
            }
        }
    }

    // Popup düzenleme formunu gösteren metod
    public function popup_update($id) {
        $data['popup'] = $this->Popup_model->get(array("id" => $id));

        if (empty($data['popup'])) {
            // Eğer popup bulunamazsa veya geçersiz ID ise ana sayfaya yönlendir
            $this->session->set_flashdata("popup_error", "Düzenlenecek popup bulunamadı.");
            redirect(base_url('admin/popups'));
        }

        $this->load->view("admin/popups/update", $data); // Bu görünüm dosyasını oluşturmanız gerekecek!
    }

    // Popup düzenleme formundan gelen veriyi kaydeden metod
    public function save_updated_popup($id) {
        // Form doğrulama kurallarını tanımla
        $this->form_validation->set_rules('title', 'Başlık', 'trim|required');
        $this->form_validation->set_rules('start_date', 'Başlangıç Tarihi', 'required');
        $this->form_validation->set_rules('end_date', 'Bitiş Tarihi', 'required');

        $this->form_validation->set_message('required', '{field} alanı boş bırakılamaz.');

        if ($this->form_validation->run() == FALSE) {
            // Form doğrulama başarısız olursa
            $this->session->set_flashdata("formError", true);
            $this->session->set_flashdata("title_error", form_error('title'));
            $this->session->set_flashdata("start_date_error", form_error('start_date'));
            $this->session->set_flashdata("end_date_error", form_error('end_date'));
            redirect(base_url("admin/popup_update/" . $id));
        } else {
            // Mevcut görsel adını formdan alın (input type="hidden" ile)
            $img = $this->input->post('current_image');

            // Görsel yükleme yapılandırması
            $config['upload_path']   = './uploads/popups/';
            $config['allowed_types'] = 'jpg|png|jpeg|gif';
            $config['overwrite']     = FALSE; // Varolan dosyanın üzerine yazma
            $this->load->library('upload', $config);

            // Eğer yeni bir görsel yüklenmişse
            if ($this->upload->do_upload('image')) {
                $data = $this->upload->data();
                $new_img_name = $data['file_name'];

                // Eski görseli sil (eğer yeni görsel başarıyla yüklendiyse ve eski görsel varsa)
                if (!empty($img) && file_exists(FCPATH . "uploads/popups/" . $img)) {
                    unlink(FCPATH . "uploads/popups/" . $img);
                }
                $img = $new_img_name; // Görsel adını güncelle
            } else {
                // Eğer yeni bir görsel yüklenmeye çalışıldıysa ve hata oluştuysa
                if ($_FILES['image']['name'] != "") { // Sadece dosya seçilmişse hata göster
                    $this->session->set_flashdata("formError", true);
                    $this->session->set_flashdata("image_error", $this->upload->display_errors());
                    redirect(base_url("admin/popup_update/" . $id));
                    return;
                }
            }

            $update_data = [
                'title'      => $this->input->post('title'),
                'image'      => $img, // Güncellenmiş görsel adı veya eski görsel adı
                'start_date' => $this->input->post('start_date'),
                'end_date'   => $this->input->post('end_date'),
                'status'     => $this->input->post('status') ? 1 : 0,
            ];

            if ($this->Popup_model->update($id, $update_data)) {
                $this->session->set_flashdata("popup_update_success", true);
                redirect(base_url("admin/popups"));
            } else {
                $this->session->set_flashdata("popup_update_error", "Popup güncellenirken bir hata oluştu.");
                redirect(base_url("admin/popup_update/" . $id));
            }
        }
    }

    public function delete_popup($id) {
        // 1. Silinecek popup'ın bilgilerini veritabanından çek (özellikle görsel yolunu almak için)
        $popup = $this->Popup_model->get(array("id" => $id));

        if ($popup) {
            // 2. Pop-up'ı veritabanından sil
            $delete_success = $this->Popup_model->delete(array("id" => $id));

            if ($delete_success) {
                // 3. İlişkili görseli sunucudan sil (eğer varsa)
                if (!empty($popup->image) && file_exists(FCPATH . "uploads/popups/" . $popup->image)) {
                    unlink(FCPATH . "uploads/popups/" . $popup->image);
                }

                // 4. Başarı mesajı ayarla
                $this->session->set_flashdata("popup_delete_success", true);
            } else {
                // Silme başarısız olursa (örneğin veritabanı hatası)
                $this->session->set_flashdata("popup_delete_error", "Popup silinirken bir hata oluştu.");
            }
        } else {
            // Popup bulunamazsa
            $this->session->set_flashdata("popup_delete_error", "Silinecek popup bulunamadı.");
        }

        // 5. Popup listeleme sayfasına yönlendir
        redirect(base_url("admin/popups"));
    }
}
