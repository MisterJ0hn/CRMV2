select
    `codi_ejamtest`.`cuenta`.`id` AS `cuenta_id`,
    `codi_ejamtest`.`cuenta`.`nombre` AS `compañia`,
    `codi_ejamtest`.`contrato`.`id` AS `contrato_id`,
    `codi_ejamtest`.`contrato`.`agenda_id` AS `agenda_id`,
    `codi_ejamtest`.`contrato`.`folio` AS `folio`,
    `codi_ejamtest`.`contrato`.`fecha_creacion` AS `fecha_cto`,
    `codi_ejamtest`.`contrato`.`nombre` AS `cliente`,
    `contrato`.`tramitador_id`,
    `ut`.`nombre` AS `tramitador`,
    `agenda`.`abogado_id`,
    `uc`.`nombre` AS `cerrador`,
    `codi_ejamtest`.`causa`.`id` AS `IdCausa`,
    `codi_ejamtest`.`causa`.`causa_nombre` AS `caratulado`,
    `ca`.`folio` AS `folio_activo`,
    `ca`.`vigencia` AS `vigencia_activo`,
    `ca`.`meses` AS `meses_activo`,
    `aa`.`fecha_creacion_anexo` AS `fecha_creacion_anexo`,
    `aa`.`vigencia_anexo` AS `vigencia_anexo`,
    `cm`.`folio` AS `morosos`,
    `vm`.`folio` AS `VipMayor2MM`,
    `vr`.`folio` AS `VipReferidos`,
    `vu`.`folio` AS `VipUnaCuota`,
    `cf`.`rol` AS `rol`,
    `cf`.`fecha_registro` AS `fecha_registro_observacion`,
    
    case 
        when ca.folio is not null then 1
        when aa.vigencia_anexo is not null then 1
        else
            0
    end        
    as activo,
    case 
        when cm.folio is not null then 1
        else 0
    end 
    as moroso,
    case 
        when vm.folio is not null then 1
        when vr.folio is not null then 1
        when vu.folio is not null then 1
        else 0
    end
    as vip,
    case 
        when cf.rol is null then 0 
        when cf.rol = 0 then 0 
        when cf.rol='' then 0     
        else 1
    end
    as tieneRol,
    `cf`.`causa_finalizada` AS `causa_finalizada`
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
                                                    `codi_ejamtest`.`contrato`
                                                    join `codi_ejamtest`.`agenda` on (
                                                        (
                                                            `codi_ejamtest`.`contrato`.`agenda_id` = `codi_ejamtest`.`agenda`.`id`
                                                        )
                                                    )
                                                )
                                                join `codi_ejamtest`.`cuenta` on (
                                                    (
                                                        `codi_ejamtest`.`agenda`.`cuenta_id` = `codi_ejamtest`.`cuenta`.`id`
                                                    )
                                                )
                                            )
                                            join `codi_ejamtest`.`usuario` `ut` on (
                                                (
                                                    `codi_ejamtest`.`contrato`.`tramitador_id` = `ut`.`id`
                                                )
                                            )
                                        )
                                        join `codi_ejamtest`.`usuario` `uc` on (
                                            (`codi_ejamtest`.`agenda`.`abogado_id` = `uc`.`id`)
                                        )
                                    )
                                    join `codi_ejamtest`.`causa` on (
                                        (
                                            `codi_ejamtest`.`causa`.`agenda_id` = `codi_ejamtest`.`agenda`.`id`
                                        )
                                    )
                                )
                                left join `codi_ejamtest`.`vw_causas_activas` `ca` on (
                                    (
                                        `codi_ejamtest`.`contrato`.`id` = `ca`.`contrato_id`
                                    )
                                )
                            )
                            left join `codi_ejamtest`.`vw_anexos_activos` `aa` on (
                                (
                                    `codi_ejamtest`.`contrato`.`id` = `aa`.`contrato_id`
                                )
                            )
                        )
                        left join `codi_ejamtest`.`vw_clientes_morosos` `cm` on (
                            (
                                `cm`.`contrato_id` = `codi_ejamtest`.`contrato`.`id`
                            )
                        )
                    )
                    left join `codi_ejamtest`.`vw_vip_mayor_2mm` `vm` on (
                        (
                            `vm`.`contrato_id` = `codi_ejamtest`.`contrato`.`id`
                        )
                    )
                )
                left join `codi_ejamtest`.`vw_vip_referidos` `vr` on (
                    (
                        `vr`.`contrato_id` = `codi_ejamtest`.`contrato`.`id`
                    )
                )
            )
            left join `codi_ejamtest`.`vw_vip_una_cuota` `vu` on (
                (
                    `vu`.`contrato_id` = `codi_ejamtest`.`contrato`.`id`
                )
            )
        )
        left join `codi_ejamtest`.`vw_causas_finalizadas` `cf` on ((`cf`.`causa_id` = `codi_ejamtest`.`causa`.`id`))
    )
where
    (
        isnull (`codi_ejamtest`.`contrato`.`fecha_desiste`)
        and (`codi_ejamtest`.`causa`.`estado` = 1)
        and (
            (`ca`.`folio` is not null)
            or (`aa`.`vigencia_anexo` is not null)
        )
    )



select caf.tramitador, count(*) as causas_activas,
(select count(*) from vw_causas_activas_final cafdia
    where cuenta_id = caf.cuenta_id
    and activo=1 
    and moroso=0
    and cafdia.tramitador_id=caf.tramitador_id
    group by tramitador_id) as causas_al_dia,
(select count(*) from vw_causas_activas_final
where cuenta_id = caf.cuenta_id
and activo=1
 )
    
from vw_causas_activas_final caf
where caf.cuenta_id = 7
    and caf.activo=1 
group by caf.tramitador_id

select
    cuenta_id,
    tramitador_id,
    tramitador,
  count(*) as CausasActivas,
  sum(case when moroso=0 then 1 else 0 end) as CausasAlDia,
  count(distinct case when activo=1 then contrato_id end) as ClientesActivos,
  count(distinct case when activo=1 and moroso=0 then contrato_id end) as ClientesAlDia,
  count(distinct case when moroso=1 then contrato_id end) as ClientesMorosos,
  count(distinct case when activo=1 and vip=1 then contrato_id end) as ClientesActivosVIP,
  count(distinct case when activo=1 and vip=1 and moroso=0 then contrato_id end) as ClientesAlDiaVIP,
  count(case when activo=1 and tieneRol=1 then 1 end) as CausasActivasConRol,
  count(case when activo=1 and tieneRol=0 then 1 end) as CausasActivasSinRol,
  count(case when activo=1 and causa_finalizada=1 then 1 end) as CausasActivasFinalizadas
from vw_causas_activas_final
where cuenta_id = 7
    and activo=1 
group by tramitador_id