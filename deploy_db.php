<?php

// Cấu hình
$dbmlFile = 'db.dbml';
$projectName = 'ipa-rebuild-docs'; // Tên project trên dbdocs

function deploy($dbmlFile, $projectName)
{
    // 1. Kiểm tra xem file db.dbml có tồn tại không
    if (!file_exists($dbmlFile)) {
        echo "Lỗi: Không tìm thấy file {$dbmlFile}\n";
        return;
    }

    echo "Đang bắt đầu đẩy sơ đồ từ {$dbmlFile} lên dbdocs.io...\n";

    // 2. Thực thi lệnh dbdocs build
    // Ghi chú: Cần thực hiện 'dbdocs login' trước ở terminal
    $command = "dbdocs build " . escapeshellarg($dbmlFile) . " --project " . escapeshellarg($projectName);

    // Thực thi và lấy kết quả
    $output = [];
    $returnVar = 0;
    exec($command, $output, $returnVar);

    if ($returnVar === 0) {
        echo "Chúc mừng! Cập nhật sơ đồ thành công.\n";
        echo "Bạn có thể xem tại link dbdocs của mình.\n";
    } else {
        echo "Có lỗi xảy ra khi chạy dbdocs:\n";
        echo implode("\n", $output) . "\n";
    }
}

deploy($dbmlFile, $projectName);
