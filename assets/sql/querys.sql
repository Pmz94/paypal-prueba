SELECT
	p.nombre producto,
	COALESCE(COUNT(t.id_producto), 0) AS frecuencia
FROM transacciones t
LEFT JOIN compradores c
	ON t.clave_comprador = c.clave
LEFT JOIN productos p
    ON t.id_producto = p.id
WHERE c.correo = ''
GROUP BY p.id
ORDER BY p.id;