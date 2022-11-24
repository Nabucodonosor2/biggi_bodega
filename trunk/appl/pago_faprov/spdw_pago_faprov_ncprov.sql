---------------------------- spdw_pago_faprov_ncprov ---------------------------
ALTER procedure [dbo].[spdw_pago_faprov_ncprov](@ve_cod_pago_faprov		numeric
										,@ve_cod_empresa	numeric)
/*
Trae todas las NC ya asociadas a @ve_cod_pago_faprov
y las dispónibles de @ve_cod_empresa

si @ve_cod_pago_faprov = 0 => es registro nuevo
=> solo trae las dispónibles de @ve_cod_empresa
*/										
AS
BEGIN
	DECLARE @TEMPO TABLE  
		(SELECCION					varchar(1)
		,COD_NCPROV_PAGO_FAPROV		numeric
		,COD_PAGO_FAPROV			numeric
		,COD_NCPROV					numeric
		,NRO_NCPROV					numeric
		,FECHA_NCPROV				varchar(100)
		,TOTAL_CON_IVA				numeric
		,MONTO_ASIGNADO				numeric
		)

	-- las NCPROV ya asociadas a @ve_cod_pago_faprov
	insert into @TEMPO
		(SELECCION
		,COD_NCPROV_PAGO_FAPROV	
		,COD_PAGO_FAPROV			
		,COD_NCPROV					
		,NRO_NCPROV				
		,FECHA_NCPROV				
		,TOTAL_CON_IVA
		,MONTO_ASIGNADO				
		)
	select 'S'								--SELECCION
			,npf.COD_NCPROV_PAGO_FAPROV		--COD_NCPROV_PAGO_FAPROV
			,npf.COD_PAGO_FAPROV			--COD_PAGO_FAPROV
			,npf.COD_NCPROV					--COD_NCPROV
			,n.NRO_NCPROV					--NRO_NCPROV
			,convert(varchar, n.FECHA_NCPROV, 103) --FECHA_NCPROV
			,n.TOTAL_CON_IVA				--TOTAL_CON_IVA
			,npf.MONTO_ASIGNADO				--MONTO_ASIGNADO
	from NCPROV_PAGO_FAPROV npf, NCPROV n
	where npf.COD_PAGO_FAPROV = @ve_cod_pago_faprov
	  and n.COD_NCPROV = npf.COD_NCPROV


	if (@ve_cod_pago_faprov = 0) begin
		-- las NCPROV de @ve_cod_empresa disponibles y que no estan en @ve_cod_pago_faprov
		insert into @TEMPO
			(SELECCION
			,COD_NCPROV_PAGO_FAPROV	
			,COD_PAGO_FAPROV			
			,COD_NCPROV					
			,NRO_NCPROV				
			,FECHA_NCPROV				
			,TOTAL_CON_IVA
			,MONTO_ASIGNADO				
			)
		select	'N'								--SELECCION
				,null							--COD_NCPROV_PAGO_FAPROV
				,null							--COD_PAGO_FAPROV
				,n.COD_NCPROV					--COD_NCPROV
				,n.NRO_NCPROV					--NRO_NCPROV
				,convert(varchar, n.FECHA_NCPROV, 103) --FECHA_NCPROV
				,n.TOTAL_CON_IVA				--TOTAL_CON_IVA
				,0								--MONTO_ASIGNADO
		from NCPROV n
		where n.COD_EMPRESA = @ve_cod_empresa
		  and n.COD_ESTADO_NCPROV = 2	--confirmada
		  and n.COD_NCPROV not in
			(select COD_NCPROV
			from NCPROV_PAGO_FAPROV npf, PAGO_FAPROV pf
			where pf.COD_PAGO_FAPROV = npf.COD_PAGO_FAPROV
			and pf.COD_ESTADO_PAGO_FAPROV = 2	--confirmada
			)
	end
	else begin	--@ve_cod_pago_faprov <> 0
		declare
			@vl_cod_estado_pago_faprov		numeric
			
		select @vl_cod_estado_pago_faprov = cod_estado_pago_faprov
				,@ve_cod_empresa = COD_EMPRESA		-- NO USA EL PARAMETRO!! fuerza a que sea el cod_empresa correcto
		from pago_faprov
		where cod_pago_faprov = @ve_cod_pago_faprov
		
		if (@vl_cod_estado_pago_faprov = 1)	begin --emitida
			-- las NCPROV de @ve_cod_empresa disponibles y que no estan en @ve_cod_pago_faprov
			insert into @TEMPO
				(SELECCION
				,COD_NCPROV_PAGO_FAPROV	
				,COD_PAGO_FAPROV			
				,COD_NCPROV					
				,NRO_NCPROV				
				,FECHA_NCPROV				
				,TOTAL_CON_IVA
				,MONTO_ASIGNADO				
				)
			select	'N'								--SELECCION
					,null							--COD_NCPROV_PAGO_FAPROV
					,@ve_cod_pago_faprov			--COD_PAGO_FAPROV
					,n.COD_NCPROV					--COD_NCPROV
					,n.NRO_NCPROV					--NRO_NCPROV
					,convert(varchar, n.FECHA_NCPROV, 103) --FECHA_NCPROV
					,n.TOTAL_CON_IVA				--TOTAL_CON_IVA
					,0								--MONTO_ASIGNADO
			from NCPROV n
			where n.COD_EMPRESA = @ve_cod_empresa
			  and n.COD_ESTADO_NCPROV = 2	--confirmada
			  and n.COD_NCPROV not in
				(select COD_NCPROV
				from NCPROV_PAGO_FAPROV npf, PAGO_FAPROV pf
				where pf.COD_PAGO_FAPROV = npf.COD_PAGO_FAPROV
				and pf.COD_ESTADO_PAGO_FAPROV = 2	--confirmada
				)
			  and n.COD_NCPROV not in
				(select COD_NCPROV
				from NCPROV_PAGO_FAPROV npf
				where npf.COD_PAGO_FAPROV =  @ve_cod_pago_faprov
				)
		end
	end
	
	select *
	from @TEMPO
	order by NRO_NCPROV
END
