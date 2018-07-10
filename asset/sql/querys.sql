SELECT
	c.correo mejorComprador,
	MAX(t.fechahora) ultimoPago,
	IFNULL(comp.completos, 0) completos,
	IFNULL(pend.pendientes, 0) pendientes,
	IFNULL(dev.devoluciones, 0) devueltos,
	COUNT(t.pagoTotal) totalPagos,
	MIN(t.pagoTotal) pagoMin,
	MAX(t.pagoTotal) pagoMax,
	ROUND(AVG(t.pagoTotal), 2) gastosProm,
	ROUND(STD(t.pagoTotal), 2) desvEstGastos,
	COALESCE((SUM(t.pagoTotal) - totalDevuelto), SUM(t.pagoTotal)) totalGastado,
	IFNULL(dev.totalDevuelto, 0) totalDevuelto
FROM transacciones t
LEFT JOIN compradores c
	USING (idComprador)
LEFT JOIN (
	SELECT idComprador, COUNT(*) completos
	FROM transacciones
	WHERE estado = 1 AND devuelto = 0
	GROUP BY idComprador
) comp
	USING (idComprador)
LEFT JOIN (
	SELECT idComprador, COUNT(*) pendientes
	FROM transacciones
	WHERE estado = 3 AND devuelto = 0
	GROUP BY idComprador
) pend
	USING (idComprador)
LEFT JOIN (
	SELECT idComprador, SUM(devuelto) devoluciones, SUM(pagoTotal) totalDevuelto
	FROM transacciones
	WHERE devuelto = 1
	GROUP BY idComprador
) dev
	USING (idComprador)
GROUP BY mejorComprador
HAVING totalPagos >= 1
ORDER BY totalPagos DESC, totalGastado DESC;

SELECT s.nomServicio servicio, IFNULL(COUNT(t.servicio), 0) frecuencia
FROM transacciones t
LEFT JOIN compradores c
	ON t.idComprador = c.idComprador
LEFT JOIN servicios s
	ON t.servicio = s.idServicio
WHERE c.correo = 'pedro.munoz@deacsystems.com'
GROUP BY s.idServicio
ORDER BY s.idServicio;

SELECT
	a.expediente,
	CONCAT(a.nombre, ' ', a.apellido_paterno, ' ', a.apellido_materno) nombre,
	a.fecha_nacimiento fechanac,
	a.numero_celular cel,
	a.correo_electronico email,
	p.nombre plantel,
	d.calle,
	d.numero_exterior numext,
	d.numero_interior numint,
	d.entrecalles entre,
	d.colonia
FROM ctalumnos a
JOIN ctalumnos_domicilio d
	ON a.id = d.id_alumno
JOIN ctplanteles p
	ON a.id_plantel = p.id
WHERE p.municipio = 'HERMOSILLO' AND (a.fecha_nacimiento BETWEEN '1993-01-01' AND '1996-12-31')
	AND a.expediente IN('09070246','09010429','09180292', '09010150','11080664','12070574','12010167',
	'11010350','11080127','10010581','10070311','10180530','10180154','09080594','10080105','10070287',
	'09010162','09070371','09080494','09070044','09010485','09010481','10070574','09080690','09080611',
	'09080404','10070456','10070361','10180093','10010158')
ORDER BY fechanac;