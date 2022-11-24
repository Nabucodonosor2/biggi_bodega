---------------- spdw_envio_resumen --------------
alter PROCEDURE spdw_envio_resumen(@ve_cod_envio_softland	numeric
									,@ve_cod_tipo_envio		numeric)
AS
BEGIN

declare @TEMPO TABLE     --creación de variable tipo tabla temporal
   (RE_CANT_FA				numeric
	,RE_TOTAL_NETO_FA		numeric
	,RE_MONTO_IVA_FA		numeric
	,RE_TOTAL_FA			numeric
	,RE_CANT_NC				numeric
	,RE_TOTAL_NETO_NC		numeric
	,RE_MONTO_IVA_NC		numeric
	,RE_TOTAL_NC			numeric
	,RE_DIF_MESES			varchar(100))

declare
	@cant_fa			numeric
	,@total_neto_fa		numeric
	,@monto_iva_fa		numeric
	,@total_fa			numeric
	,@cant_nc			numeric
	,@total_neto_nc		numeric
	,@monto_iva_nc		numeric
	,@total_nc			numeric
	,@dif_meses			varchar(100)
	,@count				numeric

insert into @TEMPO
	(RE_CANT_FA				,RE_TOTAL_NETO_FA		,RE_MONTO_IVA_FA		,RE_TOTAL_FA			
	,RE_CANT_NC				,RE_TOTAL_NETO_NC		,RE_MONTO_IVA_NC		,RE_TOTAL_NC
	,RE_DIF_MESES)
values
	(null					,null					,null					,null
	,null					,null					,null					,null
	,null)

if (@ve_cod_envio_softland is null) begin -- new record
	if (@ve_cod_tipo_envio=1) begin 
		-- es el resumen de los select que estan en php en dw_lista_factura y dw_lista_nota_credito
		-- factura
		select @cant_fa = isnull(count(*), 0)
				,@total_neto_fa = isnull(sum(TOTAL_NETO), 0)
				,@monto_iva_fa = isnull(sum(MONTO_IVA), 0)
				,@total_fa = isnull(sum(TOTAL_CON_IVA), 0)
		from factura
		where COD_ESTADO_DOC_SII in (2,3,4)
		  and COD_FACTURA not in (select COD_FACTURA from ENVIO_FACTURA EF, ENVIO_SOFTLAND E where E.COD_ESTADO_ENVIO <> 3 and E.COD_ENVIO_SOFTLAND = EF.COD_ENVIO_SOFTLAND)

		--notas credito
		select @cant_nc = isnull(count(*), 0)
				,@total_neto_nc = isnull(sum(TOTAL_NETO), 0)
				,@monto_iva_nc = isnull(sum(MONTO_IVA), 0)
				,@total_nc = isnull(sum(TOTAL_CON_IVA), 0)
		from nota_credito
		where COD_ESTADO_DOC_SII in (2,3,4)
		  and COD_NOTA_CREDITO not in (select COD_NOTA_CREDITO from ENVIO_NOTA_CREDITO EN, ENVIO_SOFTLAND E where E.COD_ESTADO_ENVIO <> 3 and E.COD_ENVIO_SOFTLAND = EN.COD_ENVIO_SOFTLAND)

		-- Verifia si existen FA o NC de diferenctes meses
		select @count = count(MES) 
		from (select distinct month(fecha_factura) MES
			  from factura
			  where COD_ESTADO_DOC_SII in (2,3,4)
				and COD_FACTURA not in (select COD_FACTURA from ENVIO_FACTURA EF, ENVIO_SOFTLAND E where E.COD_ESTADO_ENVIO <> 3 and E.COD_ENVIO_SOFTLAND = EF.COD_ENVIO_SOFTLAND)
			  union
			  select distinct month(fecha_nota_credito) MES
			  from nota_credito
			  where COD_ESTADO_DOC_SII in (2,3,4)
				and COD_NOTA_CREDITO not in (select COD_NOTA_CREDITO from ENVIO_NOTA_CREDITO EN, ENVIO_SOFTLAND E where E.COD_ESTADO_ENVIO <> 3 and E.COD_ENVIO_SOFTLAND = EN.COD_ENVIO_SOFTLAND)
			) A
		if (@count > 1)
			set @dif_meses = 'Existen Facturas o Nota de Crédito de diferentes meses'
		else
			set @dif_meses = null
	end
	else if (@ve_cod_tipo_envio=2) begin 
		-- es el resumen de los select que estan en php en dw_lista_factura_compra y dw_lista_nota_credito_compra
		-- factura
		select @cant_fa = isnull(count(*), 0)
				,@total_neto_fa = isnull(sum(TOTAL_NETO), 0)
				,@monto_iva_fa = isnull(sum(MONTO_IVA), 0)
				,@total_fa = isnull(sum(TOTAL_CON_IVA), 0)
		from faprov
		where COD_ESTADO_FAPROV = 2 -- aprobada
			and COD_CUENTA_COMPRA is not null
		  	and COD_FAPROV not in (select COD_FAPROV from ENVIO_FAPROV EF, ENVIO_SOFTLAND E where E.COD_ESTADO_ENVIO <> 3 and E.COD_ENVIO_SOFTLAND = EF.COD_ENVIO_SOFTLAND)

		--notas credito
		select @cant_nc = isnull(count(*), 0)
				,@total_neto_nc = isnull(sum(TOTAL_NETO), 0)
				,@monto_iva_nc = isnull(sum(MONTO_IVA), 0)
				,@total_nc = isnull(sum(TOTAL_CON_IVA), 0)
		from ncprov
		where COD_ESTADO_NCPROV = 2 -- aprobada
			and COD_CUENTA_COMPRA is not null
		  	and COD_NCPROV not in (select COD_NCPROV from ENVIO_NCPROV EN, ENVIO_SOFTLAND E where E.COD_ESTADO_ENVIO <> 3 and E.COD_ENVIO_SOFTLAND = EN.COD_ENVIO_SOFTLAND)

		-- Verifia si existen FA o NC de diferenctes meses
		select @count = count(MES) 
		from (select distinct month(fecha_faprov) MES
			  from faprov
			  where COD_ESTADO_FAPROV = 2 -- aprobada
				and COD_FAPROV not in (select COD_FAPROV from ENVIO_FAPROV EF, ENVIO_SOFTLAND E where E.COD_ESTADO_ENVIO <> 3 and E.COD_ENVIO_SOFTLAND = EF.COD_ENVIO_SOFTLAND)
			  union
			  select distinct month(fecha_ncprov) MES
			  from ncprov
			  where COD_ESTADO_NCPROV = 2 -- aprobada
				and COD_NCPROV not in (select COD_NCPROV from ENVIO_NCPROV EN, ENVIO_SOFTLAND E where E.COD_ESTADO_ENVIO <> 3 and E.COD_ENVIO_SOFTLAND = EN.COD_ENVIO_SOFTLAND)
			) A
		if (@count > 1)
			set @dif_meses = 'Existen Facturas o Nota de Crédito de diferentes meses'
		else
			set @dif_meses = null
	end
end
-- Envio ventas
else if (@ve_cod_tipo_envio=1) begin 
	-- facturas
	select @cant_fa = isnull(count(*), 0)
			,@total_neto_fa = isnull(sum(TOTAL_NETO), 0)
			,@monto_iva_fa = isnull(sum(MONTO_IVA), 0)
			,@total_fa = isnull(sum(TOTAL_CON_IVA), 0)
	from envio_factura ef, factura f
	where ef.cod_envio_softland = @ve_cod_envio_softland
	  and f.cod_factura = ef.cod_factura

	--notas credito
	select @cant_nc = isnull(count(*), 0)
			,@total_neto_nc = isnull(sum(TOTAL_NETO), 0)
			,@monto_iva_nc = isnull(sum(MONTO_IVA), 0)
			,@total_nc = isnull(sum(TOTAL_CON_IVA), 0)
	from envio_nota_credito en, nota_credito n
	where en.cod_envio_softland = @ve_cod_envio_softland
	  and n.cod_nota_credito = en.cod_nota_credito

	-- Verifia si existen FA o NC de diferenctes meses
	select @count = count(MES) 
	from (select distinct month(fecha_factura) MES
		  from envio_factura ef, factura f
		  where ef.cod_envio_softland = @ve_cod_envio_softland
			and f.cod_factura = ef.cod_factura
		  union
		  select distinct month(fecha_nota_credito) MES
		  from envio_nota_credito en, nota_credito n
		  where en.cod_envio_softland = @ve_cod_envio_softland
			and n.cod_nota_credito = en.cod_nota_credito
		) A
	if (@count > 1)
		set @dif_meses = 'Existen Facturas o Nota de Crédito de diferentes meses'
	else
		set @dif_meses = null
end 
-- Envio compras
else if (@ve_cod_tipo_envio=2) begin 
	-- facturas
	select @cant_fa = isnull(count(*), 0)
			,@total_neto_fa = isnull(sum(TOTAL_NETO), 0)
			,@monto_iva_fa = isnull(sum(MONTO_IVA), 0)
			,@total_fa = isnull(sum(TOTAL_CON_IVA), 0)
	from envio_faprov ef, faprov f
	where ef.cod_envio_softland = @ve_cod_envio_softland
	  and f.cod_faprov = ef.cod_faprov

	--notas credito
	select @cant_nc = isnull(count(*), 0)
			,@total_neto_nc = isnull(sum(TOTAL_NETO), 0)
			,@monto_iva_nc = isnull(sum(MONTO_IVA), 0)
			,@total_nc = isnull(sum(TOTAL_CON_IVA), 0)
	from envio_ncprov en, ncprov n
	where en.cod_envio_softland = @ve_cod_envio_softland
	  and n.cod_ncprov = en.cod_ncprov

	-- Verifia si existen FA o NC de diferenctes meses
	select @count = count(MES) 
	from (select distinct month(fecha_faprov) MES
		  from envio_faprov ef, faprov f
		  where ef.cod_envio_softland = @ve_cod_envio_softland
			and f.cod_faprov = ef.cod_faprov
		  union
		  select distinct month(fecha_ncprov) MES
		  from envio_ncprov en, ncprov n
		  where en.cod_envio_softland = @ve_cod_envio_softland
			and n.cod_ncprov = en.cod_ncprov
		) A
	if (@count > 1)
		set @dif_meses = 'Existen Facturas o Nota de Crédito de diferentes meses'
	else
		set @dif_meses = null
end
else if (@ve_cod_tipo_envio=3) begin --EGRSOS
	select @cant_fa = isnull(count(*), 0)
			,@total_fa = isnull(sum(MONTO_DOCUMENTO), 0)
	from ENVIO_PAGO_FAPROV epf, PAGO_FAPROV pf
	where epf.cod_envio_softland = @ve_cod_envio_softland
	  and pf.COD_PAGO_FAPROV = epf.COD_PAGO_FAPROV 
end


update @TEMPO
set RE_CANT_FA = @cant_fa
	,RE_TOTAL_NETO_FA = @total_neto_fa
	,RE_MONTO_IVA_FA = @monto_iva_fa
	,RE_TOTAL_FA = @total_fa
	,RE_CANT_NC = @cant_nc
	,RE_TOTAL_NETO_NC = @total_neto_nc
	,RE_MONTO_IVA_NC = @monto_iva_nc
	,RE_TOTAL_NC = @total_nc
	,RE_DIF_MESES = @dif_meses

SELECT * FROM @TEMPO

END