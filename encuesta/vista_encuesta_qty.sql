select
    `crm`.`encuesta`.`contrato_id` AS `contrato_id`,
    sum(
        (
            case
                when (`crm`.`encuesta`.`funcion_respuesta_id` = 1) then 1
                else 0
            end
        )
    ) AS `qty_encuesta`,
    count(0) AS `qty_gestion_encuesta`
from
    `crm`.`encuesta`
where
    (`crm`.`encuesta`.`estado_id` = 2)
group by
    `crm`.`encuesta`.`contrato_id`