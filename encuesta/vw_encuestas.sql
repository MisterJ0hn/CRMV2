select
    `crm`.`encuesta`.`contrato_id` AS `contrato_id`,
    `crm`.`funcion_encuesta`.`nombre` AS `nombre_funcion_encuesta`,
    `crm`.`funcion_respuesta`.`nombre` AS `nombre_funcion_respuesta`,
    max(`crm`.`encuesta`.`fecha_creacion`) AS `fecha_creacion`,
    max(`crm`.`encuesta`.`usuario_creacion_id`) AS `usuario_creacion_id`,
    `crm`.`encuesta`.`observacion` AS `observacion`,
    `vista_nota_max`.`nota` AS `nota`,
    `crm`.`encuesta`.`fecha_cierre` AS `fecha_cierre`,
    `crm`.`encuesta_preguntas`.`pregunta` AS `pregunta`,
    `crm`.`encuesta_preguntas`.`respuesta_abierta` AS `respuesta_abierta`
from
    (
        (
            (
                (
                    `crm`.`encuesta`
                    join `crm`.`funcion_respuesta` on (
                        (
                            `crm`.`encuesta`.`funcion_respuesta_id` = `crm`.`funcion_respuesta`.`id`
                        )
                    )
                )
                join `crm`.`funcion_encuesta` on (
                    (
                        `crm`.`funcion_respuesta`.`funcion_encuesta_id` = `crm`.`funcion_encuesta`.`id`
                    )
                )
            )
            join `crm`.`vista_nota_max` on (
                (
                    `vista_nota_max`.`encuesta_id` = `crm`.`encuesta`.`id`
                )
            )
        )
        join `crm`.`encuesta_preguntas` on (
            (
                `crm`.`encuesta_preguntas`.`encuesta_id` = `crm`.`encuesta`.`id`
            )
        )
    )
where
    (
        (`crm`.`encuesta`.`funcion_respuesta_id` = 1)
        and (`crm`.`encuesta`.`estado_id` = 2)
        and (`crm`.`encuesta_preguntas`.`tipo_pregunta` = 3)
    )
group by
    `crm`.`encuesta`.`contrato_id`