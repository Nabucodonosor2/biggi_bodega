-------------------------- sp_fa_arriendo --------------------------
alter PROCEDURE sp_fa_arriendo(@ve_lista_contrato		varchar(8000)
								,@ve_agrupar_contrato	varchar(1)
								,@ve_cod_usuario		numeric)
AS
/*
Crea facturas de arriendo para la lista de contratos @ve_lista_contrato
@ve_lista_contrato : lista de contratos de la forma "cod_contrato1|cod_contrato2|....|cod_contratoN"
@ve_agrupar_contrato : 'S' indica que crea facturas agrpadas donde cada linea es un contrato de arrindo
						'N' indica que se debe hacer 1 contrato una factura, donde los items son los items del contrato
						NO usado por ahora SIMEPRE viene en 'S'
*/
BEGIN  
	declare C_CONTRATO CURSOR FOR  
	select item 
	from  f_split(@ve_lista_contrato, '|')	

	declare
		@vc_cod_arriendo		numeric
		,@vl_cod_factura		numeric
		,@vl_orden				numeric
		,@vc_cod_item_arriendo	numeric
		,@vc_cod_producto		varchar(30)
		,@vc_nom_producto		varchar(100)
		,@vc_cantidad			T_CANTIDAD
		,@vc_precio				numeric

	set @vl_cod_factura = null
	set @vl_orden = 1
	OPEN C_CONTRATO
	FETCH C_CONTRATO INTO @vc_cod_arriendo
	WHILE @@FETCH_STATUS = 0 BEGIN	
		if (@vl_cod_factura is null) begin		-- 1ra vez que entra al loop se debe crear FA
			declare 
				@K_FA_RENTAL				numeric
				,@K_PARAM_IVA				numeric
				,@vl_cod_empresa			numeric
				,@vl_cod_persona			numeric
				,@vl_referencia				varchar(100) 
				,@vl_cod_sucursal_factura	numeric
				,@vl_porc_iva				T_PORCENTAJE
				,@vl_nom_forma_pago_otro	varchar(100)

			set @K_FA_RENTAL = 2
			set @K_PARAM_IVA = 1
			set @vl_porc_iva = convert(decimal, dbo.f_get_parametro(@K_PARAM_IVA))
			select @vl_cod_empresa = cod_empresa
				,@vl_cod_persona = cod_persona
				,@vl_cod_sucursal_factura = cod_sucursal
			from arriendo
			where cod_arriendo = @vc_cod_arriendo

			select @vl_referencia = 'CONTRATOS DE ARRIENDO  DE ' + upper(m.nom_mes)
			from mes m
			where m.cod_mes = month(getdate())

			execute spu_factura 
			'INSERT' 					-- ve_operacion
			,NULL 						-- ve_cod_factura = identity
			,NULL 						-- cod_usuario_impresion
			,@ve_cod_usuario 
			,NULL 						-- ve_nro_factura
			,NULL						-- FECHA_FACTURA	
			,1 							-- cod_estado_doc_sii = emitida
			,@vl_cod_empresa 
			,@vl_cod_sucursal_factura	-- ve_cod_sucursal_factura*
			,@vl_cod_persona
			,@vl_referencia 
			,null						--nro_orden_compra
			,NULL						--fecha_orden_compra_cliente
			,NULL 						-- obs
			,NULL 						-- retirado_por
			,NULL 						-- rut_retirado_por
			,NULL 						-- dig_verif_retirado_por
			,NULL 						-- guia_transporte
			,NULL 						-- patente
			,NULL 						-- cod_bodega
			,@K_FA_RENTAL				-- cod_tipo_factura = arriendo
			,null						--cod_doc
			,NULL 						-- motivo_anula
			,NULL 						-- cod_usuario_anula 
			,@ve_cod_usuario --vendedor1
			,0	--@porc_vendedor1			
			,null					--vendedor 2
			,0	--porc_vendedor2		
			,7	--cod_forma_pago = CCF-30 DIAS
			,null	-- cod_origen_venta		
			,0	--subtotal
			,0	--porc_dscto1		
			,'P' 	
			,0 -- monto_dscto1		
			,0	--porc_dscto2		
			,'P' 	
			,0 -- monto_dscto2		
			,0	--total_neto			
			,@vl_porc_iva			
			,0	--monto_iva			 
			,0	--total_con_iva		
			,NULL					--@ve_porc_factura_parcial
			,null	--nom_forma_pago_otro
			,'N'	-- genera_salida
			,'ARRIENDO'	
			,'N'	--CANCELADA

			set @vl_cod_factura = @@identity
		end

		declare C_ITEM CURSOR FOR  
		select i.cod_item_arriendo
				,i.cod_producto
				,i.nom_producto
				,dbo.f_bodega_stock(i.cod_producto, a.cod_bodega, getdate())
				,i.precio
		from item_arriendo i, arriendo a
		where i.cod_arriendo = @vc_cod_arriendo
		  and a.cod_arriendo = i.cod_arriendo
		  and dbo.f_bodega_stock(i.cod_producto, a.cod_bodega, getdate()) > 0
  		order by i.orden

		OPEN C_ITEM
		FETCH C_ITEM INTO @vc_cod_item_arriendo, @vc_cod_producto, @vc_nom_producto, @vc_cantidad, @vc_precio
		WHILE @@FETCH_STATUS = 0 BEGIN	
			insert into ITEM_FACTURA
				(COD_FACTURA
				,ORDEN
				,ITEM
				,COD_PRODUCTO
				,NOM_PRODUCTO
				,CANTIDAD
				,PRECIO
				,COD_ITEM_DOC
				,COD_TIPO_TE
				,MOTIVO_TE
				,TIPO_DOC
				)
			values
				(@vl_cod_factura				--COD_FACTURA
				,@vl_orden						--ORDEN
				,convert(varchar, @vl_orden)	--ITEM
				,@vc_cod_producto				--COD_PRODUCTO
				,@vc_nom_producto				--NOM_PRODUCTO
				,@vc_cantidad					--CANTIDAD
				,@vc_precio						--PRECIO
				,@vc_cod_item_arriendo			--COD_ITEM_DOC
				,null							--COD_TIPO_TE
				,null							--MOTIVO_TE
				,'ITEM_ARRIENDO'				--TIPO_DOC
				)
			set @vl_orden = @vl_orden + 1

			FETCH C_ITEM INTO @vc_cod_item_arriendo, @vc_cod_producto, @vc_nom_producto, @vc_cantidad, @vc_precio
		END
		CLOSE C_ITEM
		DEALLOCATE C_ITEM		

		FETCH C_CONTRATO INTO @vc_cod_arriendo
	END
	CLOSE C_CONTRATO
	DEALLOCATE C_CONTRATO		

	execute spu_factura'RECALCULA', @vl_cod_factura
end
