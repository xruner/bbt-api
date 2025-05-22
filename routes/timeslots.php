<?php
header("Content-Type: application/json");

$db = new Database();
$connection = $db->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            // 获取常规时段
            $query = "SELECT * FROM regular_timeslots";
            $stmt = $connection->prepare($query);
            $stmt->execute();
            $regularSlots = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 获取特殊时段
            $query = "SELECT * FROM special_timeslots";
            $stmt = $connection->prepare($query);
            $stmt->execute();
            $specialSlots = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'regular' => $regularSlots,
                    'special' => $specialSlots
                ]
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => '获取时段数据失败: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'POST':
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data || !isset($data['start']) || !isset($data['end'])) {
                throw new Exception('无效的请求数据');
            }
            
            $table = isset($data['date']) ? 'special_timeslots' : 'regular_timeslots';
            
            $query = "INSERT INTO $table 
                     (start_time, end_time, date, enabled) 
                     VALUES 
                     (:start, :end, :date, :enabled)";
                     
            $stmt = $connection->prepare($query);
            
            $stmt->bindParam(':start', $data['start']);
            $stmt->bindParam(':end', $data['end']);
            $stmt->bindParam(':enabled', $data['enabled'] ?? true, PDO::PARAM_BOOL);
            
            if (isset($data['date'])) {
                $stmt->bindParam(':date', $data['date']);
            } else {
                $stmt->bindValue(':date', null);
            }
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => '时段添加成功',
                    'id' => $connection->lastInsertId()
                ]);
            } else {
                throw new Exception('时段添加失败');
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
            
            if (!$data || !isset($data['id']) || !isset($data['start']) || !isset($data['end'])) {
                throw new Exception('无效的请求数据');
            }
            
            $table = isset($data['date']) ? 'special_timeslots' : 'regular_timeslots';
            
            $query = "UPDATE $table 
                     SET start_time = :start, 
                         end_time = :end,
                         enabled = :enabled
                     WHERE id = :id";
                     
            $stmt = $connection->prepare($query);
            
            $stmt->bindParam(':start', $data['start']);
            $stmt->bindParam(':end', $data['end']);
            $stmt->bindParam(':enabled', $data['enabled'] ?? true, PDO::PARAM_BOOL);
            $stmt->bindParam(':id', $data['id']);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => '时段更新成功'
                ]);
            } else {
                throw new Exception('时段更新失败');
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
            
            if (!$data || !isset($data['id']) || !isset($data['type'])) {
                throw new Exception('无效的请求数据');
            }
            
            $table = $data['type'] === 'special' ? 'special_timeslots' : 'regular_timeslots';
            
            $query = "DELETE FROM $table WHERE id = :id";
            $stmt = $connection->prepare($query);
            $stmt->bindParam(':id', $data['id']);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => '时段删除成功'
                ]);
            } else {
                throw new Exception('时段删除失败');
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