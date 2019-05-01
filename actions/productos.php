<?php

require_once '../Funciones.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
	try {
		$servicios = include_once '../config/servicios.php';

		switch(strtolower($_POST['cmd'])) {
			case 'ver':
				$query = "
					SELECT *
					FROM productos
					ORDER BY nombre
				";

				$query = $servicios->db->prepare($query);
				$query->execute();

				//while($row = $query->fetch(PDO::FETCH_ASSOC)) {
				//	$data[] = $row;
				//}
				foreach($query->fetchAll(PDO::FETCH_ASSOC) as $i => $row) {
					$data[] = $row;
				}

				$func = new Funciones();
				$data = $func->convertir_de_latin1_a_utf8_recursivo($data);
				$count = count($data);
				break;

			case 'crear':
				$nombre = trim($_POST['nombre']) ?? null;
				$precio = ($_POST['precio'] > 0) ? $_POST['precio'] : null;

				$query = "
					INSERT INTO productos(nombre, precio)
					VALUES
						(:nombre, :precio)
				";
				$values = [
					'nombre' => $nombre,
					'precio' => $precio
				];

				$query = $servicios->db->prepare($query);
				$query->execute($values);

				$data = 'Producto agregado';
				$count = 1;
				break;

			case 'editar':
				break;

			case 'borrar':
				$id_producto = trim($_POST['id_producto']) ?? null;

				$query = "DELETE FROM productos WHERE id = ?";

				$query = $servicios->db->prepare($query);
				$query->execute([$id_producto]);

				$data = 'Producto borrado';
				$count = 1;
				break;
		}

		$output = [
			'status' => true,
			'code' => 200,
			'count' => $count,
			'data' => $data
		];
		header('HTTP/1.1 200 OK; Content-type: application/json; charset=UTF-8');
		echo json_encode($output, JSON_PRETTY_PRINT);

	} catch(Exception $e) {
		$output = [
			'status' => false,
			'code' => $e->getCode(),
			'message' => $e->getMessage()
		];
		header('HTTP/1.1 400 Exception');
		echo json_encode($output, JSON_PRETTY_PRINT);
	}
} else {
	$output = [
		'status' => false,
		'code' => 405,
		'message' => 'Nel'
	];
	header('HTTP/1.1 405 Method not allowed');
	echo json_encode($output, JSON_PRETTY_PRINT);
}