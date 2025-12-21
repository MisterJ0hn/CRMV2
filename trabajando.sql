select 
	`c`.`id` AS `id`, 
	`c`.`estado_civil_id` AS `estado_civil_id`, 
	`c`.`situacion_laboral_id` AS `situacion_laboral_id`, 
	`c`.`estrategia_juridica_id` AS `estrategia_juridica_id`, 
	`c`.`escritura_id` AS `escritura_id`, 
	`c`.`agenda_id` AS `agenda_id`, 
	`c`.`nombre` AS `nombre`, 
	`c`.`email` AS `email`, 
	`c`.`telefono` AS `telefono`, 
	`c`.`ciudad` AS `ciudad`, 
	`c`.`rut` AS `rut`, 
	`c`.`direccion` AS `direccion`, 
	`c`.`comuna` AS `comuna`, 
	`c`.`titulo_contrato` AS `titulo_contrato`, 
	`c`.`monto_nivel_deuda` AS `monto_nivel_deuda`, 
	`c`.`monto_contrato` AS `monto_contrato`, 
	`c`.`cuotas` AS `cuotas`, 
	`c`.`valor_cuota` AS `valor_cuota`, 
	`c`.`interes` AS `interes`, 
	`c`.`dia_pago` AS `dia_pago`, 
	(
		case when (
			(
				select 
					count(0) 
				from 
					`contrato_anexo` `a` 
				where 
					(`a`.`contrato_id` = `c`.`id`)
			) > 0
		) then (
			select 
				concat(`a`.`fecha_creacion`) AS `fecha_creacion` 
			from 
				`contrato_anexo` `a` 
			where 
				(`a`.`contrato_id` = `c`.`id`) 
			order by 
				`a`.`id` desc 
			limit 
				1
		) else `c`.`fecha_creacion` end
	) AS `fecha_creacion`, 
	`c`.`sucursal_id` AS `sucursal_id`, 
	`c`.`tramitador_id` AS `tramitador_id`, 
	`c`.`cliente_id` AS `cliente_id`, 
	`c`.`clave_unica` AS `clave_unica`, 
	`c`.`telefono_recado` AS `telefono_recado`, 
	`c`.`fecha_primer_pago` AS `fecha_primer_pago`, 
	`c`.`pais_id` AS `pais_id`, 
	`c`.`vehiculo_id` AS `vehiculo_id`, 
	`c`.`vivienda_id` AS `vivienda_id`, 
	`c`.`reunion_id` AS `reunion_id`, 
	`c`.`pdf` AS `pdf`, 
	`c`.`is_abono` AS `is_abono`, 
	`c`.`primera_cuota` AS `primera_cuota`, 
	`c`.`fecha_primera_cuota` AS `fecha_primera_cuota`, 
	`c`.`observacion` AS `observacion`, 
	`c`.`fecha_ultimo_pago` AS `fecha_ultimo_pago`, 
	`c`.`is_finalizado` AS `is_finalizado`, 
	`c`.`lote` AS `lote`, 
	`c`.`pdf_termino` AS `pdf_termino`, 
	`c`.`fecha_termino` AS `fecha_termino`, 
	`c`.`vigencia` AS `vigencia`, 
	`c`.`fecha_desiste` AS `fecha_desiste`, 
	`c`.`fecha_pdf_anexo` AS `fecha_pdf_anexo`, 
	`c`.`fecha_compromiso` AS `fecha_compromiso`, 
	`c`.`ultima_funcion` AS `ultima_funcion`, 
	`c`.`q_mov` AS `q_mov`, 
	`c`.`id_lote_id` AS `id_lote_id`, 
	`c`.`ccomuna_id` AS `ccomuna_id`, 
	`c`.`cciudad_id` AS `cciudad_id`, 
	`c`.`cregion_id` AS `cregion_id`, 
	`c`.`sexo` AS `sexo`, 
	`c`.`is_anexo` AS `is_anexo`, 
	`c`.`proximo_vencimiento` AS `proximo_vencimiento`, 
	`c`.`fecha_ultima_gestion` AS `fecha_ultima_gestion`, 
	`c`.`pago_actual` AS `pago_actual`, 
	`c`.`is_total` AS `is_total`, 
	`c`.`cartera_orden` AS `cartera_orden`, 
	`c`.`cartera_id` AS `cartera_id`, 
	`c`.`is_incorporacion` AS `is_incorporacion`, 
	`c`.`grupo_id` AS `grupo_id`, 
	`c`.`estado_encuesta_id` AS `estado_encuesta_id`, 
	`c`.`observacion_encuesta` AS `observacion_encuesta`, 
	`c`.`fecha_encuesta` AS `fecha_encuesta`, 
	`c`.`qty_encuesta` AS `qty_encuesta`, 
	`c`.`qty_gestion_encuesta` AS `qty_gestion_encuesta`, 
	(
		case when (
			(
				select 
					count(0) 
				from 
					`contrato_anexo` `a` 
				where 
					(`a`.`contrato_id` = `c`.`id`)
			) > 0
		) then (
			select 
				concat(
					`a`.`id`, '-', `c`.`folio`, '-', `a`.`folio`
				) AS `folio` 
			from 
				`contrato_anexo` `a` 
			where 
				(`a`.`contrato_id` = `c`.`id`) 
			order by 
				`a`.`id` desc 
			limit 
				1
		) else `c`.`folio` end
	) AS `folio`, 
	`c`.`folio` AS `folio_contrato` ,
    (select nota from encuesta e join encuesta_preguntas p on p.encuesta_id=e.id where e.contrato_id=c.id and p.tipo_pregunta=1 order by p.id Desc Limit 0,1) as ultima_nota
from 
	`contrato` `c`



INSERT INTO `codi_ejamtest`.`modulo` (`id`, `nombre`, `ruta`, `nombre_alt`, `descripcion`) VALUES (NULL, 'pago_resumen_excel', 'pago_resumen_excel', 'Pagos resumen excel', 'Resumen de pagos');
INSERT INTO `codi_ejamtest`.`modulo` (`id`, `nombre`, `ruta`, `nombre_alt`, `descripcion`) VALUES (NULL, 'cobranza_excel', 'cobranza_excel', 'cobranza_excel', 'Excel cobranza');

INSERT INTO `codi_ejamtest`.`funcion_respuesta` (`id`, `funcion_encuesta_id`, `nombre`) VALUES 
(NULL, '1', 'Cliente Conforme'), 
(NULL, '1', 'Cliente no Conforme (genera nuevo ticket)');