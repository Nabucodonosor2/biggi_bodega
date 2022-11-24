--------------- spi_resultado --------------
ALTER PROCEDURE spi_resultado(@ve_cod_usuario			numeric
								,@ve_ano				numeric
								,@ve_cod_centro_costo	varchar(10))
AS
BEGIN

declare
	 @vl_fecha_actual		datetime
	,@vc_fecha_actual		datetime
	,@vc_cod_usuario		numeric
	,@vc_cod_nota_venta		numeric
	,@vc_fecha_nota_venta	datetime
	,@vc_total_neto			T_PRECIO
	,@vc_porc_resultado		T_PORCENTAJE
	,@vc_monto_resultado	T_PRECIO
	,@vc_porc_aa			T_PORCENTAJE
	,@vc_monto_aa			T_PRECIO
	,@vc_pago_aa			T_PRECIO
	,@vc_porc_gv			T_PORCENTAJE
	,@vc_monto_gv			T_PRECIO
	,@vc_pago_gv			T_PRECIO
	,@vc_porc_adm			T_PORCENTAJE
	,@vc_monto_adm			T_PRECIO
	,@vc_pago_adm			T_PRECIO
	,@vc_porc_v1			T_PORCENTAJE
	,@vc_monto_v1			T_PRECIO
	,@vc_pago_v1			T_PRECIO

	set @vl_fecha_actual = getdate()
	
	-- borra el resultado de informes anteriores del mismo usuario
	delete INF_RESULTADO
	where cod_usuario = @ve_cod_usuario

	if (@ve_cod_centro_costo='001')
		declare C_INF_RESULTADO cursor for
		select	 @vl_fecha_actual
				,@ve_cod_usuario
				,COD_NOTA_VENTA       
				,FECHA_NOTA_VENTA    
				,TOTAL_NETO 
				,dbo.f_nv_get_resultado(cod_nota_venta, 'PORC_RESULTADO')	--PORC_RESULTADO       
				,dbo.f_nv_get_resultado(cod_nota_venta, 'RESULTADO')		--MONTO_RESULTADO      
				,dbo.f_get_parametro_porc('AA', FECHA_NOTA_VENTA)			--PORC_AA      
				,dbo.f_nv_get_resultado(cod_nota_venta, 'MONTO_DIRECTORIO')	--MONTO_AA      
				,dbo.f_nv_get_resultado(cod_nota_venta, 'PAGO_DIRECTORIO')	--PAGO_AA      
				,dbo.f_get_parametro_porc('GV', FECHA_NOTA_VENTA)			--PORC_GV      
				,dbo.f_nv_get_resultado(cod_nota_venta, 'COMISION_GV')		--MONTOGV      
				,dbo.f_nv_get_resultado(cod_nota_venta, 'PAGO_GV')			--PAGO_GV      
				,dbo.f_get_parametro_porc('ADM', FECHA_NOTA_VENTA)			--PORC_ADM      
				,dbo.f_nv_get_resultado(cod_nota_venta, 'COMISION_ADM')		--MONTO_ADM      
				,dbo.f_nv_get_resultado(cod_nota_venta, 'PAGO_ADM')			--PAGO_ADM      
				,PORC_VENDEDOR1	+ PORC_VENDEDOR2							--PORC_V1      
				,dbo.f_nv_get_resultado(cod_nota_venta, 'COMISION_V1')		--MONTO_VENDEDOR      
				+dbo.f_nv_get_resultado(cod_nota_venta, 'COMISION_V2')
				,dbo.f_nv_get_resultado(cod_nota_venta, 'PAGO_VENDEDOR')	--PAGO_VENDEDOR      
		FROM	NOTA_VENTA
		WHERE	COD_ESTADO_NOTA_VENTA = 2	-- cerrada
		  and	year(FECHA_NOTA_VENTA) = @ve_ano
		  and	COD_EMPRESA NOT IN (SELECT COD_EMPRESA FROM CENTRO_COSTO_EMPRESA WHERE COD_CENTRO_COSTO <> '001')
	else
		declare C_INF_RESULTADO cursor for
		select	 @vl_fecha_actual
				,@ve_cod_usuario
				,COD_NOTA_VENTA       
				,FECHA_NOTA_VENTA    
				,TOTAL_NETO 
				,dbo.f_nv_get_resultado(cod_nota_venta, 'PORC_RESULTADO')	--PORC_RESULTADO       
				,dbo.f_nv_get_resultado(cod_nota_venta, 'RESULTADO')		--MONTO_RESULTADO      
				,dbo.f_get_parametro_porc('AA', FECHA_NOTA_VENTA)			--PORC_AA      
				,dbo.f_nv_get_resultado(cod_nota_venta, 'MONTO_DIRECTORIO')	--MONTO_AA      
				,dbo.f_nv_get_resultado(cod_nota_venta, 'PAGO_DIRECTORIO')	--PAGO_AA      
				,dbo.f_get_parametro_porc('GV', FECHA_NOTA_VENTA)			--PORC_GV      
				,dbo.f_nv_get_resultado(cod_nota_venta, 'COMISION_GV')		--MONTOGV      
				,dbo.f_nv_get_resultado(cod_nota_venta, 'PAGO_GV')			--PAGO_GV      
				,dbo.f_get_parametro_porc('ADM', FECHA_NOTA_VENTA)			--PORC_ADM      
				,dbo.f_nv_get_resultado(cod_nota_venta, 'COMISION_ADM')		--MONTO_ADM      
				,dbo.f_nv_get_resultado(cod_nota_venta, 'PAGO_ADM')			--PAGO_ADM      
				,PORC_VENDEDOR1	+ PORC_VENDEDOR2							--PORC_V1      
				,dbo.f_nv_get_resultado(cod_nota_venta, 'COMISION_V1')		--MONTO_VENDEDOR      
				+dbo.f_nv_get_resultado(cod_nota_venta, 'COMISION_V2')
				,dbo.f_nv_get_resultado(cod_nota_venta, 'PAGO_VENDEDOR')	--PAGO_VENDEDOR      
		FROM	NOTA_VENTA
		WHERE	COD_ESTADO_NOTA_VENTA = 2	-- cerrada
		  and	year(FECHA_NOTA_VENTA) = @ve_ano
		  and	COD_EMPRESA IN (SELECT COD_EMPRESA FROM CENTRO_COSTO_EMPRESA WHERE COD_CENTRO_COSTO = @ve_cod_centro_costo)

	OPEN C_INF_RESULTADO
	FETCH C_INF_RESULTADO INTO   @vc_fecha_actual	,@vc_cod_usuario	,@vc_cod_nota_venta		,@vc_fecha_nota_venta
								,@vc_total_neto		,@vc_porc_resultado	,@vc_monto_resultado	,@vc_porc_aa
								,@vc_monto_aa		,@vc_pago_aa		,@vc_porc_gv			,@vc_monto_gv
								,@vc_pago_gv		,@vc_porc_adm		,@vc_monto_adm			,@vc_pago_adm
								,@vc_porc_v1		,@vc_monto_v1		,@vc_pago_v1
	WHILE @@FETCH_STATUS = 0
	BEGIN
		insert into	INF_RESULTADO
				(FECHA_INF_RESULTADO	,COD_USUARIO		,COD_NOTA_VENTA			,FECHA_NOTA_VENTA
				,TOTAL_NETO 			,PORC_RESULTADO     ,MONTO_RESULTADO		,PORC_AA
				,MONTO_AA				,PAGO_AA			,PORC_GV				,MONTO_GV              
				,PAGO_GV				,PORC_ADM			,MONTO_ADM				,PAGO_ADM             
				,PORC_VENDEDOR			,MONTO_VENDEDOR		,PAGO_VENDEDOR)
		values  (@vc_fecha_actual		,@vc_cod_usuario	,@vc_cod_nota_venta		,@vc_fecha_nota_venta
				,@vc_total_neto			,@vc_porc_resultado	,@vc_monto_resultado	,@vc_porc_aa
				,@vc_monto_aa			,@vc_pago_aa		,@vc_porc_gv			,@vc_monto_gv
				,@vc_pago_gv			,@vc_porc_adm		,@vc_monto_adm			,@vc_pago_adm
				,@vc_porc_v1			,@vc_monto_v1		,@vc_pago_v1)

		FETCH C_INF_RESULTADO INTO   @vc_fecha_actual	,@vc_cod_usuario	,@vc_cod_nota_venta		,@vc_fecha_nota_venta
									,@vc_total_neto		,@vc_porc_resultado	,@vc_monto_resultado	,@vc_porc_aa
									,@vc_monto_aa		,@vc_pago_aa		,@vc_porc_gv			,@vc_monto_gv
									,@vc_pago_gv		,@vc_porc_adm		,@vc_monto_adm			,@vc_pago_adm
									,@vc_porc_v1		,@vc_monto_v1		,@vc_pago_v1
	END
	CLOSE C_INF_RESULTADO
	DEALLOCATE C_INF_RESULTADO
END
