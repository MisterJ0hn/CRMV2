SELECT
    contrato.id as contrato_id,
    contrato.folio,
    contrato.vigencia,
    contrato.fecha_creacion
FROM
    contrato
WHERE
    timestampdiff (MONTH, contrato.fecha_creacion, now ()) <= contrato.vigencia
    AND contrato.fecha_desiste IS NULL



SELECT
    cuenta.nombre as compañia,
    contrato.id as contrato_id,
    contrato.agenda_id,
    contrato.folio,
    contrato.fecha_creacion as fecha_cto,
    contrato.nombre as cliente,
    ut.nombre as tramitador,
    uc.nombre as cerrador,
    causa.id as IdCausa,
    causa.causa_nombre as caratulado,
    ca.folio as folio_activo,
    ca.vigencia as vigencia_activo,
    ca.meses as meses_activo,
    aa.fecha_creacion_anexo as fecha_creacion_anexo,
    aa.vigencia_anexo as vigencia_anexo,
    cm.folio as morosos,
    vm.folio as VipMayor2MM,
    vr.folio as VipReferidos,
    vu.folio as VipUnaCuota,
    cf.rol,
    cf. causa_finalizada,
    cf.fecha_registro as fecha_registro_ult_observacion
FROM
    contrato
    inner join agenda on contrato.agenda_id = agenda.id
    inner join cuenta on agenda.cuenta_id = cuenta.id
    inner join usuario ut on contrato.tramitador_id = ut.id
    inner join usuario uc on agenda.abogado_id = uc.id
    inner join causa on causa.agenda_id = agenda.id
    left join vw_causas_activas ca on contrato.id = ca.contrato_id
    left join vw_anexos_activos aa on contrato.id = aa.contrato_id
    left join vw_clientes_morosos cm on cm.contrato_id=contrato.id
    left join vw_vip_mayor_2mm vm on vm.contrato_id = contrato.id
    left join vw_vip_referidos vr on vr.contrato_id = contrato.id
    left join vw_vip_una_cuota vu on vu.contrato_id = contrato.id
    left join vw_causas_finalizadas cf on cf.causa_id=causa.id
WHERE
    cuenta.id = 7
    AND contrato.fecha_desiste IS NULL
    AND causa.estado = 1
    and (ca.folio is not null or aa.vigencia_anexo is not null)
group by contrato.folio 


    
select
    `codi_ejamtest`.`contrato_anexo`.`contrato_id` AS `contrato_id`,
    max(`codi_ejamtest`.`contrato_anexo`.`fecha_creacion`) AS `max_fecha`
from
    `codi_ejamtest`.`contrato_anexo`
where
    (
        ifnull (`codi_ejamtest`.`contrato_anexo`.`is_desiste`, 0) <> 1
    )
group by
    `codi_ejamtest`.`contrato_anexo`.`contrato_id`


select
    `ca`.`contrato_id` AS `contrato_id`,
    `ca`.`fecha_creacion` AS `fecha_creacion_anexo`,
    `ca`.`vigencia` AS `vigencia_anexo`,
    `ca`.`folio` AS `numero_anexo`
from
    (
        `codi_ejamtest`.`contrato_anexo` `ca`
        join `codi_ejamtest`.`vw_max_fecha_anexo` `vmfa` on (
            (
                (`ca`.`contrato_id` = `vmfa`.`contrato_id`)
                and (`ca`.`fecha_creacion` = `vmfa`.`max_fecha`)
            )
        )
    )
where
    (ifnull (`ca`.`is_desiste`, 0) <> 1)
    and ca.fecha_creacion BETWEEN date_sub ('2025-06-26', INTERVAL ca.vigencia Month) AND '2025-06-26 23:59'



select
    id AS `contrato_id`,
    folio,
    vigencia,
    fecha_creacion,
    timestampdiff (
        MONTH,
        fecha_creacion,
        now()
    ) AS meses
from
    contrato
where
    isnull (fecha_desiste)
        and fecha_creacion BETWEEN date_sub ('2025-06-26', INTERVAL vigencia Month) AND '2025-06-26 23:59'








        fecha de observación causa)
SELECT causa.id as IdCausa, causa.id_causa as rol, causa.causa_finalizada, causa_observacion.fecha_registro
FROM causa, causa_observacion
WHERE causa.estado = 1 AND causa.id = causa_observacion.causa_id
ORDER BY causa_observacion.fecha_registro DESC