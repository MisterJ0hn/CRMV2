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
    coalesce(`ca`.`fecha_creacion`, `c`.`fecha_creacion`) AS `fecha_creacion`,
    coalesce(
        concat (`ca`.`id`, '-', `c`.`folio`, '-', `ca`.`folio`),
        `c`.`folio`
    ) AS `folio`,
    `c`.`folio` AS `folio_contrato`,
    `e1`.`fecha_creacion` AS `fecha_encuesta`,
    `e1`.`usuario_creacion_id` AS `usuario_encuesta_id`,
    `e1`.`nombre_funcion_respuesta` AS `encuesta_funcion_respuesta`,
    `e1`.`nombre_funcion_encuesta` AS `encuesta_funcion_encuesta`,
    `e1`.`observacion` AS `encuesta_observacion`,
    `e1`.`fecha_cierre` AS `encuesta_fecha_cierre`,
    `e1`.`pregunta` AS `encuesta_pregunta`,
    `e1`.`respuesta_abierta` AS `encuesta_respuesta_abierta`,
    `e1`.`nota` AS `encuesta_nota`,
    `e2`.`fecha_creacion` AS `fecha_gestion`,
    `e2`.`usuario_creacion_id` AS `usuario_gestion_id`,
    `e2`.`nombre_funcion_respuesta` AS `gestion_funcion_respuesta`,
    `e2`.`nombre_funcion_encuesta` AS `gestion_funcion_encuesta`,
    `e2`.`observacion` AS `gestion_observacion`,
    coalesce(`eq`.`qty_encuesta`, 0) AS `qty_encuesta`,
    `eq`.`qty_gestion_encuesta` AS `qty_gestion_encuesta`,
    `nm`.`nota` AS `ultima_nota`,
    `uc`.`nombre` AS `usuario_calidad`,
    `cp`.`fecha_pago` AS `fecha_pago`,
    `cp`.`monto` AS `monto`,
    `cp`.`numero` AS `numero`,
    `cp`.`vencimiento_id` AS `vencimiento_id`,
    (
        case
            when (`vm`.`folio` is not null) then 1
            when (`vr`.`folio` is not null) then 1
            when (`vu`.`folio` is not null) then 1
            else 0
        end
    ) AS `vip`,
    `c`.`estado_suscripcion` AS `estado_suscripcion`,
    `c`.`acepta_suscripcion` AS `acepta_suscripcion`,
    `vuolt`.`fecha_registro` AS `fecha_ult_observacion`,
    `vuolt`.`observacion` AS `ult_observacion`,
    coalesce(
        (
            to_days (now ()) - to_days (`vuolt`.`fecha_registro`)
        ),
        0
    ) AS `dias_ult_observacion`,
    (
        case
            when (
                timestampdiff (MONTH, `c`.`fecha_creacion`, now ()) <= `c`.`vigencia`
            ) then 1
            else 0
        end
    ) AS `vigencia_contrato`,
    (
        case
            when isnull (`ca`.`id`) then NULL
            when (
                timestampdiff (MONTH, `cax`.`fecha_creacion`, now ()) <= `cax`.`vigencia`
            ) then 1
            else 0
        end
    ) AS `vigencia_anexo`,
    (
        case
            when isnull (`cm`.`contrato_id`) then 0
            else 1
        end
    ) AS `moroso`
from
    (
        (
            (
                (
                    (
                        (
                            (
                                (
                                    (
                                        (
                                            (
                                                (
                                                    (
                                                        `codi_ejamtest`.`contrato` `c`
                                                        left join `codi_ejamtest`.`vista_contrato_anexo_max` `ca` on ((`ca`.`contrato_id` = `c`.`id`))
                                                    )
                                                    left join `codi_ejamtest`.`vw_encuestas` `e1` on ((`e1`.`contrato_id` = `c`.`id`))
                                                )
                                                left join `codi_ejamtest`.`vw_gestiones` `e2` on ((`e2`.`contrato_id` = `c`.`id`))
                                            )
                                            left join `codi_ejamtest`.`vista_encuesta_qty` `eq` on ((`eq`.`contrato_id` = `c`.`id`))
                                        )
                                        left join `codi_ejamtest`.`vista_nota_max` `nm` on ((`nm`.`contrato_id` = `c`.`id`))
                                    )
                                    left join `codi_ejamtest`.`vista_usuario_calidad` `uc` on ((`uc`.`grupo_id` = `c`.`grupo_id`))
                                )
                                left join `codi_ejamtest`.`vw_cuota_pendiente` `cp` on ((`cp`.`contrato_id` = `c`.`id`))
                            )
                            left join `codi_ejamtest`.`vw_vip_mayor_2mm` `vm` on ((`vm`.`contrato_id` = `c`.`id`))
                        )
                        left join `codi_ejamtest`.`vw_vip_referidos` `vr` on ((`vr`.`contrato_id` = `c`.`id`))
                    )
                    left join `codi_ejamtest`.`vw_vip_una_cuota` `vu` on ((`vu`.`contrato_id` = `c`.`id`))
                )
                left join `codi_ejamtest`.`vw_ult_observacion_linea_tiempo` `vuolt` on ((`vuolt`.`contrato_id` = `c`.`id`))
            )
            left join `codi_ejamtest`.`contrato_anexo` `cax` on ((`cax`.`id` = `ca`.`id`))
        )
        left join `codi_ejamtest`.`vw_clientes_morosos` `cm` on ((`cm`.`contrato_id` = `c`.`id`))
    )