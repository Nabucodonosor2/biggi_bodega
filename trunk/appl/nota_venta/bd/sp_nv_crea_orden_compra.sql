create PROCEDURE sp_nv_crea_orden_compra(@ve_cod_nota_venta numeric)
AS
BEGIN
	
	declare	@vl_cod_usuario_confirma		numeric
			, @vl_cod_usuario_vendedor1		numeric	
			, @vl_cod_moneda				numeric
			, @kl_cod_estado_oc				numeric
			, @vl_cod_nota_venta			numeric	
			, @vl_cod_cuenta_corriente		numeric
			, @vl_referencia				varchar(100)
			, @vl_cod_sucursal_factura		numeric
			, @vl_subtotal					numeric
			, @vl_total_neto				numeric
			, @vl_porc_iva					numeric
			, @vl_monto_iva					numeric
			, @vl_total_con_iva				numeric
			, @kl_forma_pago_efectivo		numeric
			, @vl_cod_orden_compra			numeric
			, @vl_cod_item_nota_venta		numeric
			, @vl_orden						numeric
			, @vl_item						varchar(10)
			, @vl_cod_producto				varchar(30)
			, @vl_nom_producto				varchar(100)
			, @vl_cantidad					T_CANTIDAD
			, @vl_precio_compra				numeric
			, @vl_cod_proveedor				numeric
			, @vl_cod_proveedor_ant			numeric
			, @kl_param_porc				numeric
			, @vl_cod_proveedor_ant_ant		numeric
			, @ve_es_proveedor_interno		varchar(1)

	set @vl_cod_proveedor_ant	 = 0
	set @kl_cod_estado_oc		 = 1
	set @kl_param_porc			 = 1

	declare C_TEMPO cursor for
	SELECT	nv.cod_usuario_confirma
			, nv.cod_usuario_vendedor1 
			, nv.cod_moneda
			, nv.cod_nota_venta
			, nv.cod_cuenta_corriente
			, nv.referencia
			, s.cod_sucursal
			, dbo.f_get_parametro(@kl_param_porc)porc_iva
			, poc.cod_item_nota_venta
			, poc.cod_producto
			,case poc.cod_producto
				when 'TE' then inv.nom_producto
				else p.nom_producto
			end nom_producto
			, poc.cantidad
			, isnull(poc.precio_compra, 0)
			, poc.cod_empresa
			, (select es_proveedor_interno from empresa where cod_empresa = poc.cod_empresa) es_proveedor_interno
	from    pre_orden_compra poc, item_nota_venta inv, nota_venta nv, sucursal s, producto p
	where	nv.cod_nota_venta = @ve_cod_nota_venta
			and inv.cod_nota_venta = nv.cod_nota_venta
			and	poc.cod_item_nota_venta = inv.cod_item_nota_venta
			and	poc.cod_empresa = s.cod_empresa	
			and poc.cod_producto = p.cod_producto
			and poc.genera_compra = 'S'
	order by poc.cod_empresa desc	

	set @vl_cod_orden_compra = 0
	OPEN C_TEMPO
	FETCH C_TEMPO INTO	@vl_cod_usuario_confirma
						, @vl_cod_usuario_vendedor1
						, @vl_cod_moneda
						, @vl_cod_nota_venta
						, @vl_cod_cuenta_corriente
						, @vl_referencia
						, @vl_cod_sucursal_factura
						, @vl_porc_iva
						, @vl_cod_item_nota_venta
						, @vl_cod_producto
						, @vl_nom_producto
						, @vl_cantidad
						, @vl_precio_compra
						, @vl_cod_proveedor
						, @ve_es_proveedor_interno
	WHILE @@FETCH_STATUS = 0
	BEGIN
	
		if (@vl_cod_proveedor_ant <> @vl_cod_proveedor) begin
			
			if(@ve_es_proveedor_interno = 'N')
			begin
				set @vl_referencia = 'NOTA DE VENTA: '+ convert(varchar(20),@ve_cod_nota_venta)
			end
			
			if (@vl_cod_orden_compra <> 0)
				exec spu_orden_compra 'RECALCULA', @vl_cod_orden_compra

			exec spu_orden_compra 'INSERT'	
									, Null		 
									, @vl_cod_usuario_confirma			-- @ve_cod_usuario		 	 
									, @vl_cod_usuario_vendedor1			-- @ve_cod_usuario_solicita	 
									, @vl_cod_moneda					-- @ve_cod_moneda				 
									, @kl_cod_estado_oc					-- @ve_cod_estado_orden_compra 
									, @vl_cod_nota_venta				-- @ve_cod_nota_venta			 
									, @vl_cod_cuenta_corriente			-- @ve_cod_cuenta_corriente 	 
									, @vl_referencia					-- @ve_referencia				 
									, @vl_cod_proveedor					-- @ve_cod_empresa			 
									, @vl_cod_sucursal_factura			-- @ve_cod_suc_factura		 
									, NULL								-- @ve_cod_persona			 
									, 0									-- @ve_sub_total
									, NULL									-- @ve_porc_dscto1
									, 0									-- @ve_monto_dscto1
									, NULL									-- @ve_porc_dscto1
									, 0									-- @ve_monto_dscto2
									, 0									-- @ve_total_neto				 
									, @vl_porc_iva						-- @ve_porc_iva				 
									, 0									-- @ve_monto_iva				 
									, 0									-- @ve_total_con_iva			 
									, null								-- @ve_obs					 
									, null								-- @ve_motivo_anula			 
									, null								-- @ve_cod_usuario_anula		 
									, null								-- @ve_ingreso_usuario_dscto1  
									, null								-- @ve_ingreso_usuario_dscto2
									, 'NOTA_VENTA'						-- @ve_tipo_nota_credito	

			set @vl_cod_orden_compra  = @@identity
			set @vl_cod_proveedor_ant_ant = @vl_cod_proveedor_ant
			set @vl_cod_proveedor_ant = @vl_cod_proveedor
		end
			
		-- llena variables para asignar item orden a item_orden_compra
		select @vl_item = count(*)+1,
			@vl_orden = (count(*)+1) * 10
		from item_orden_compra
		where cod_orden_compra =  @vl_cod_orden_compra

		exec spu_item_orden_compra 'INSERT' 
									, Null --@vl_cod_item_nota_venta			-- @ve_cod_item_orden_compra 
									, @vl_cod_orden_compra				-- @ve_cod_orden_compra 
									, @vl_orden							-- @ve_orden 
									, @vl_item							-- @ve_item 
									, @vl_cod_producto					-- @ve_cod_producto 
									, @vl_nom_producto					-- @ve_nom_producto 
									, @vl_cantidad						-- @ve_cantidad 
									, @vl_precio_compra					-- @ve_precio
									, null								-- @ve_cod_tipo_te
									, null								-- @ve_motivo_te
									, @vl_cod_item_nota_venta			-- @ve_cod_item_nota_venta

		FETCH C_TEMPO INTO @vl_cod_usuario_confirma
						, @vl_cod_usuario_vendedor1
						, @vl_cod_moneda
						, @vl_cod_nota_venta
						, @vl_cod_cuenta_corriente
						, @vl_referencia
						, @vl_cod_sucursal_factura
						, @vl_porc_iva
						, @vl_cod_item_nota_venta
						, @vl_cod_producto
						, @vl_nom_producto
						, @vl_cantidad
						, @vl_precio_compra
						, @vl_cod_proveedor		
						, @ve_es_proveedor_interno
	END
	CLOSE C_TEMPO
	DEALLOCATE C_TEMPO

	if (@vl_cod_orden_compra <> 0)
		exec spu_orden_compra 'RECALCULA', @vl_cod_orden_compra
END
