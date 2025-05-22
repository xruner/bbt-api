<?php
header("Content-Type: application/json");

$db = new Database();
$connection = $db->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            // 获取系统配置
            $query = "SELECT * FROM system_settings LIMIT 1";
            $stmt = $connection->prepare($query);
            $stmt->execute();
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$settings) {
                // 如果不存在则创建默认配置
                $query = "INSERT INTO system_settings 
                         (business_hours, appointment_interval, max_appointments_per_day, cancellation_policy) 
                         VALUES 
                         ('09:00-18:00', 30, 20, '需提前24小时取消')";
                $connection->exec($query);
                
                $settings = [
                    'business_hours' => '09:00-18:00',
                    'appointment_interval' => 30,
                    'max_appointments_per_day' => 20,
                    'cancellation_policy' => '需提前24小时取消'
                ];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $settings
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => '获取系统配置失败: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'PUT':
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                throw new Exception('无效的请求数据');
            }
            
            $query = "UPDATE system_settings 
                     SET business_hours = :business_hours,
                         appointment_interval = :appointment_interval,
                         max_appointments_per_day = :max_appointments,
                         cancellation_policy = :cancellation_policy";
                     
            $stmt = $connection->prepare($query);
            
            $stmt->bindParam(':business_hours', $data['business_hours']);
            $stmt->bindParam(':appointment_interval', $data['appointment_interval']);
            $stmt->bindParam(':max_appointments', $data['max_appointments_per_day']);
            $stmt->bindParam(':cancellation_policy', $data['cancellation_policy']);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => '系统配置更新成功'
                ]);
            } else {
                throw new Exception('系统配置更新失败');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        break;
        
    default:
        header("HTTP/1.1 405 Method Not Allowed");
        echo json_encode([
            'success' => false,
            'message' => '不支持的请求方法'
        ]);
        break;
}
?>