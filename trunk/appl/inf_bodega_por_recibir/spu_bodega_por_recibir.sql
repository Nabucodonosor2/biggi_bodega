----------------- spu_bodega_por_recibir ---------------------------------	
ALTER PROCEDURE [dbo].[spu_bodega_por_recibir](@ve_cod_usuario			numeric)
-- @ve_cod_solicitud_compra: cero indica TODAS
AS
BEGIN
	declare @vl_fecha_actual		datetime

	set @vl_fecha_actual = getdate()

    delete inf_bodega_por_recibir
	where FECHA_INF_BODEGA_POR_RECIBIR < DATEADD(DAY, -10, @vl_fecha_actual)

	-- borra el resultado de informes anteriores del mismo usuario
	delete inf_bodega_por_recibir
	where cod_usuario = @ve_cod_usuario

	--------------------------
	-- inserta las solicitudes
	insert into inf_bodega_por_recibir
	   (FECHA_INF_BODEGA_POR_RECIBIR
        ,COD_USUARIO
        ,COD_SOL_COMPRA
		,FECHA_SOL_COMPRA	
		,TIPO
		,NRO_OC
		,ORDEN
		,ALIAS_PROV
		,COD_PRODUCTO				
		,NOM_PRODUCTO				
		,CANT_SOLICITADA			
		,CANT_RECIBIDA				
		,CANT_POR_RECIBIR			
		,TERMINADO_COMPUESTO
        ,PRECIO_COMPRA
		)
	select @vl_fecha_actual
        ,@ve_cod_usuario
        ,S.COD_SOLICITUD_COMPRA	
		,convert(varchar, FECHA_SOLICITUD_COMPRA, 103)
		,'SC'
		,null  --NRO_OC
		,null	--ORDEN
		,null	--ALIAS_PROV
		,S.COD_PRODUCTO
		,P.NOM_PRODUCTO
		,S.CANTIDAD
		,dbo.f_sol_recibido(s.cod_solicitud_compra)	--CANT_RECIBIDA
		,0	--CANT_POR_RECIBIR
		,S.TERMINADO_COMPUESTO
        ,0
	from SOLICITUD_COMPRA S, PRODUCTO P 
	where P.COD_PRODUCTO = S.COD_PRODUCTO
	  and dbo.f_sol_por_llegar(s.cod_solicitud_compra) > 0
	  and S.COD_ESTADO_SOLICITUD_COMPRA = 2	-- aprobado

	-----------------------------------------
	-- Llena el campo orden con un correlativo
	declare
		@vl_orden			numeric
		,@vc_cod_sol_compra	numeric
	
	set @vl_orden = 1
	declare C_TEMPO cursor for
	select	COD_SOL_COMPRA
	FROM	inf_bodega_por_recibir
	order by COD_SOL_COMPRA

	OPEN C_TEMPO
	FETCH C_TEMPO INTO @vc_cod_sol_compra
	WHILE @@FETCH_STATUS = 0 BEGIN
		update inf_bodega_por_recibir
		set ORDEN = @vl_orden
		where COD_SOL_COMPRA = @vc_cod_sol_compra

		set @vl_orden = @vl_orden + 1

		FETCH C_TEMPO INTO @vc_cod_sol_compra
	END
	CLOSE C_TEMPO
	DEALLOCATE C_TEMPO

	----------------------------------
	-- Busca las OC de las solicitudes
	insert into inf_bodega_por_recibir
	   (FECHA_INF_BODEGA_POR_RECIBIR
        ,COD_USUARIO
        ,COD_SOL_COMPRA
		,FECHA_SOL_COMPRA	
		,TIPO
		,NRO_OC
		,ORDEN
		,ALIAS_PROV
		,COD_PRODUCTO				
		,NOM_PRODUCTO				
		,CANT_SOLICITADA			
		,CANT_RECIBIDA				
		,CANT_POR_RECIBIR	
		,TERMINADO_COMPUESTO
        ,PRECIO_COMPRA
        ,COD_PRODUCTO_COMPUESTO		
		)
	select   @vl_fecha_actual
            ,@ve_cod_usuario
            ,t.COD_SOL_COMPRA	
			,t.FECHA_SOL_COMPRA
			,'OC'
			,o.COD_ORDEN_COMPRA
			,t.orden
			,e.alias
			,null--it.cod_producto
			,it.nom_producto
			,it.cantidad
			,dbo.f_oc_recibido (it.cod_item_orden_compra)	--CANT_RECIBIDA
			,0	--CANT_POR_RECIBIR
			,null
            ,it.PRECIO
            ,it.cod_producto

	from orden_compra o, inf_bodega_por_recibir t, item_orden_compra it, empresa e
	where o.tipo_orden_compra = 'SOLICITUD_COMPRA'
	  and o.cod_estado_orden_compra in (1,3)
	  and t.COD_SOL_COMPRA = o.cod_doc
	  and t.TIPO = 'SC'
	  and t.TERMINADO_COMPUESTO = 'C'
	  and it.cod_orden_compra = o.cod_orden_compra
	  and e.cod_empresa = o.cod_empresa
      and t.COD_USUARIO = @ve_cod_usuario  
	-------------------------
	-- calcula el por recibir
	update inf_bodega_por_recibir 
	set CANT_POR_RECIBIR = CANT_SOLICITADA - CANT_RECIBIDA

	-- para las SC de equipo TERMINADO obtiene la OC y el proveedor para que salga en la misma linea
	DECLARE C_TEMPO CURSOR FOR 
	SELECT  COD_SOL_COMPRA
	  FROM  inf_bodega_por_recibir
	  WHERE TIPO = 'SC'
	  and TERMINADO_COMPUESTO = 'T'
	
	declare
		@vl_nro_oc		numeric
		,@alias_prov	varchar(100)

	OPEN C_TEMPO 
	FETCH C_TEMPO INTO @vc_cod_sol_compra
	WHILE @@FETCH_STATUS = 0 BEGIN
		select  @vl_nro_oc = o.COD_ORDEN_COMPRA
				,@alias_prov = e.alias
		from orden_compra o, empresa e
		where o.tipo_orden_compra = 'SOLICITUD_COMPRA'
		  and o.cod_estado_orden_compra in (1,3)
		  and o.cod_doc = @vc_cod_sol_compra
		  and e.cod_empresa = o.cod_empresa
		  and o.ES_OC_MANUAL <> 'S'

		update inf_bodega_por_recibir
		set NRO_OC = @vl_nro_oc
			,ALIAS_PROV = @alias_prov
		where COD_SOL_COMPRA = @vc_cod_sol_compra
		  and TIPO = 'SC'
		  and TERMINADO_COMPUESTO = 'T'

		FETCH C_TEMPO INTO @vc_cod_sol_compra
	END
	CLOSE C_TEMPO
	DEALLOCATE C_TEMPO	

END
GO
