<?php
header("Content-Type: application/json");

$db = new Database();
$connection = $db->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            $endpoint = $_GET['endpoint'] ?? '';
            
            if ($endpoint === 'rules') {
                // 获取通知规则
                $query = "SELECT * FROM notification_rules";
                $stmt = $connection->prepare($query);
                $stmt->execute();
                $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'data' => $rules
                ]);
            } elseif ($endpoint === 'history') {
                // 获取通知历史
                $query = "SELECT * FROM notification_history ORDER BY sent_at DESC";
                $stmt = $connection->prepare($query);
                $stmt->execute();
                $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'data' => $history
                ]);
            } else {
                throw new Exception('无效的端点');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        break;
        
    case 'POST':
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data || !isset($data['type']) || !isset($data['method'])) {
                throw new Exception('无效的请求数据');
            }
            
            $query = "INSERT INTO notification_rules 
                     (type, method, time_before, enabled) 
                     VALUES 
                     (:type, :method, :time_before, :enabled)";
                     
            $stmt = $connection->prepare($query);
            
            $stmt->bindParam(':type', $data['type']);
            $stmt->bindParam(':method', $data['method']);
            $stmt->bindParam(':time_before', $data['time_before'] ?? 24);
            $stmt->bindParam(':enabled', $data['enabled'] ?? true, PDO::PARAM_BOOL);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => '通知规则添加成功',
                    'id' => $connection->lastInsertId()
                ]);
            } else {
                throw new Exception('通知规则添加失败');
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
            
            if (!$data || !isset($data['id'])) {
                throw new Exception('无效的请求数据');
            }
            
            $query = "UPDATE notification_rules 
                     SET type = :type,
                         method = :method,
                         time_before = :time_before,
                         enabled = :enabled
                     WHERE id = :id";
                     
            $stmt = $connection->prepare($query);
            
            $stmt->bindParam(':type', $data['type']);
            $stmt->bindParam(':method', $data['method']);
            $stmt->bindParam(':time_before', $data['time_before']);
            $stmt->bindParam(':enabled', $data['enabled'], PDO::PARAM_BOOL);
            $stmt->bindParam(':id', $data['id']);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => '通知规则更新成功'
                ]);
            } else {
                throw new Exception('通知规则更新失败');
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
            
            $query = "DELETE FROM notification_rules WHERE id = :id";
            $stmt = $connection->prepare($query);
            $stmt->bindParam(':id', $data['id']);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => '通知规则删除成功'
                ]);
            } else {
                throw new Exception('通知规则删除失败');
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