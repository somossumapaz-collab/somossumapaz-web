<?php
// api/submit_resume.php
header('Content-Type: application/json');
require_once '../database_functions.php';

$upload_folder = '../uploads/';
if (!file_exists($upload_folder)) {
    mkdir($upload_folder, 0777, true);
}

function allowed_file($filename)
{
    $allowed = ['png', 'jpg', 'jpeg', 'pdf'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $allowed);
}

function save_file($file_key, $prefix = '')
{
    global $upload_folder;
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        $filename = basename($_FILES[$file_key]['name']);
        if (allowed_file($filename)) {
            $safe_filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
            $new_filename = $prefix . '_' . time() . '_' . $safe_filename;
            if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $upload_folder . $new_filename)) {
                return $new_filename;
            }
        }
    }
    return null;
}

try {
    $data = [
        'full_name' => $_POST['full_name'] ?? '',
        'document_id' => $_POST['document_id'] ?? '',
        'id_type' => $_POST['id_type'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'birth_date' => $_POST['birth_date'] ?? '',
        'birth_department' => $_POST['birth_department'] ?? '',
        'birth_city' => $_POST['birth_city'] ?? '',
        'department' => $_POST['department'] ?? '',
        'city' => $_POST['city'] ?? '',
        'profile_description' => $_POST['profile_description'] ?? '',
        'niche' => $_POST['niche'] ?? '',
        'skills' => $_POST['skills'] ?? '',
        'personal_references_json' => $_POST['personal_references_json'] ?? '[]',
        'family_references_json' => $_POST['family_references_json'] ?? '[]'
    ];

    $data['photo'] = save_file('photo', 'photo');
    $data['id_file_path'] = save_file('id_file', 'id');

    // Education
    $education_list = [];
    $edu_count = 0;
    while (isset($_POST["education_{$edu_count}_institution"])) {
        $edu_item = [
            'level' => $_POST["education_{$edu_count}_level"] ?? '',
            'institution' => $_POST["education_{$edu_count}_institution"] ?? '',
            'start_date' => $_POST["education_{$edu_count}_start_date"] ?? '',
            'end_date' => $_POST["education_{$edu_count}_end_date"] ?? '',
            'is_current' => isset($_POST["education_{$edu_count}_is_current"]) ? 1 : 0
        ];
        $edu_item['certificate_path'] = save_file("education_{$edu_count}_file", "edu_{$edu_count}");
        $education_list[] = $edu_item;
        $edu_count++;
    }

    // Experience
    $experience_list = [];
    $exp_count = 0;
    while (isset($_POST["experience_{$exp_count}_company"])) {
        $exp_item = [
            'role' => $_POST["experience_{$exp_count}_role"] ?? '',
            'company' => $_POST["experience_{$exp_count}_company"] ?? '',
            'start_date' => $_POST["experience_{$exp_count}_start_date"] ?? '',
            'end_date' => $_POST["experience_{$exp_count}_end_date"] ?? '',
            'is_current' => isset($_POST["experience_{$exp_count}_is_current"]) ? 1 : 0
        ];
        $exp_item['certificate_path'] = save_file("experience_{$exp_count}_file", "exp_{$exp_count}");
        $experience_list[] = $exp_item;
        $exp_count++;
    }

    $resume_id = add_resume_comprehensive($data, $education_list, $experience_list);

    if ($resume_id) {
        echo json_encode(['status' => 'success', 'message' => 'Hoja de vida guardada exitosamente', 'resume_id' => $resume_id]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar en la base de datos']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>