select
    `e`.`contrato_id` AS `contrato_id`,
    max(`p`.`nota`) AS `nota`,
    `e`.`id` AS `encuesta_id`
from
    (
        `crm`.`encuesta` `e`
        join `crm`.`encuesta_preguntas` `p` on ((`p`.`encuesta_id` = `e`.`id`))
    )
where
    (`p`.`tipo_pregunta` = 1)
group by
    `e`.`contrato_id`