<?php
header("Content-Type: application/json");

$db = new Database();
$connection = $db->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            // 获取预约列表
            $query = "SELECT * FROM appointments";
            $stmt = $connection->prepare($query);
            $stmt->execute();
            
            $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $appointments
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => '获取预约列表失败: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'POST':
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                throw new Exception('无效的请求数据');
            }
            
            $query = "INSERT INTO appointments 
                     (customer_name, phone, email, type, date, time, status) 
                     VALUES 
                     (:name, :phone, :email, :type, :date, :time, 'pending')";
                     
            $stmt = $connection->prepare($query);
            
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':type', $data['type']);
            $stmt->bindParam(':date', $data['date']);
            $stmt->bindParam(':time', $data['time']);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => '预约创建成功',
                    'id' => $connection->lastInsertId()
                ]);
            } else {
                throw new Exception('预约创建失败');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        break;
        
    case 'PUT':
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data || !isset($data['id']) || !isset($data['status'])) {
                throw new Exception('无效的请求数据');
            }
            
            $query = "UPDATE appointments 
                     SET status = :status 
                     WHERE id = :id";
                     
            $stmt = $connection->prepare($query);
            
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':id', $data['id']);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => '预约状态已更新'
                ]);
            } else {
                throw new Exception('更新失败');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        break;
        
    case 'DELETE':
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data || !isset($data['id'])) {
                throw new Exception('无效的请求数据');
            }
            
            $query = "DELETE FROM appointments WHERE id = :id";
            $stmt = $connection->prepare($query);
            $stmt->bindParam(':id', $data['id']);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => '预约已删除'
                ]);
            } else {
                throw new Exception('删除失败');
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