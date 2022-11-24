-- PROCEDIMIENTO QUE CARGA NV NULAS EN SISTEMA BIGGI 
CREATE PROCEDURE [dbo].[sp_crea_nv_nula]
			(@ve_cod_nv		numeric 
			,@ve_fecha_nota_venta varchar(10))
AS
BEGIN
	DECLARE @vl_cod_usuario numeric
	DECLARE @vl_cod_estado_nota_venta numeric
	DECLARE @vl_cod_moneda numeric
	DECLARE @vl_valor_tipo_cambio numeric(14,2)
	DECLARE @vl_cod_usuario_vendedor1 numeric
	DECLARE @vl_porc_vendedor1 numeric(5,2)
	DECLARE @vl_cod_cuenta_corriente numeric(5,2)
	DECLARE @vl_referencia varchar(100)
	DECLARE @vl_cod_empresa numeric(10,0)
	DECLARE @vl_cod_sucursal_despacho numeric(10,0)
	DECLARE @vl_cod_sucursal_factura numeric(10,0)
	DECLARE @vl_cod_persona numeric(10,0)
	DECLARE @vl_subtotal numeric(10,0)
	DECLARE @vl_porc_dscto1 numeric(5,2)
	DECLARE @vl_monto_dscto1 numeric(5,2)
	DECLARE @vl_ingreso_usuario_dscto1 varchar(1)
	DECLARE @vl_porc_dscto2 numeric(5,2)
	DECLARE @vl_monto_dscto2 numeric(5,2)
	DECLARE @vl_ingreso_usuario_dscto2 varchar(1)
	DECLARE @vl_total_neto numeric(14,2)
	DECLARE @vl_porc_iva numeric(14,2)
	DECLARE @vl_monto_iva numeric(14,2)
	DECLARE @vl_total_con_iva numeric(14,2)
	DECLARE @vl_cod_forma_pago numeric(14,2)
	DECLARE @vl_motivo_anula varchar(100)
	DECLARE @vl_cod_usuario_anula numeric(3,0)
	DECLARE @vl_porc_dscto_corporativo numeric(5,2)
	
	-- asignacion a variables

	SET @vl_cod_usuario = 1
	SET @vl_cod_estado_nota_venta = 3
	SET @vl_cod_moneda =1
	SET @vl_valor_tipo_cambio = 1.00
	SET @vl_cod_usuario_vendedor1 = 1
	SET @vl_porc_vendedor1 = 0.00
	SET @vl_cod_cuenta_corriente = 1
	SET @vl_referencia = 'NULA'
	SET @vl_cod_empresa = 1337
	SET @vl_cod_sucursal_despacho = 1337
	SET @vl_cod_sucursal_factura = 1337
	SET @vl_cod_persona = 2338
	SET @vl_subtotal = 0.00
	SET @vl_porc_dscto1 = 0.00
	SET @vl_monto_dscto1 = 0.00
	SET @vl_ingreso_usuario_dscto1 = 'M'
	SET @vl_porc_dscto2 = 0.00
	SET @vl_monto_dscto2 = 0.00
	SET @vl_ingreso_usuario_dscto2 = 'M'
	SET @vl_total_neto = 0.00
	SET @vl_porc_iva = 19.00
	SET @vl_monto_iva = 0.00
	SET @vl_total_con_iva = 0.00
	SET @vl_cod_forma_pago = 2
	SET @vl_motivo_anula = 'NULA'
	SET @vl_cod_usuario_anula = 1
	SET @vl_porc_dscto_corporativo = 0.00
	
	IF NOT EXISTS (SELECT cod_nota_venta FROM NOTA_VENTA WHERE cod_nota_venta = @ve_cod_nv)
		BEGIN
			SET IDENTITY_INSERT NOTA_VENTA ON
			INSERT INTO NOTA_VENTA(cod_nota_venta
									, fecha_nota_venta
									, fecha_registro
									, cod_usuario
									, cod_estado_nota_venta
									, cod_moneda
									, valor_tipo_cambio
									, cod_usuario_vendedor1
									, porc_vendedor1
									, cod_cuenta_corriente
									, referencia
									, cod_empresa
									, cod_sucursal_despacho
									, cod_sucursal_factura
									, cod_persona
									, fecha_plazo_cierre
									, subtotal
									, porc_dscto1
									, monto_dscto1
									, porc_dscto2
									, monto_dscto2
									, total_neto
									, porc_iva
									, monto_iva
									, total_con_iva
									, fecha_entrega
									, cod_forma_pago
									, fecha_anula
									, motivo_anula
									, cod_usuario_anula
									, porc_dscto_corporativo)
					values (@ve_cod_nv
							, @ve_fecha_nota_venta
							, getdate()
							, @vl_cod_usuario
							, @vl_cod_estado_nota_venta
							, @vl_cod_moneda
							, @vl_valor_tipo_cambio
							, @vl_cod_usuario_vendedor1
							, @vl_porc_vendedor1
							, @vl_cod_cuenta_corriente
							, @vl_referencia
							, @vl_cod_empresa
							, @vl_cod_sucursal_despacho
							, @vl_cod_sucursal_factura
							, @vl_cod_persona
							, getdate()
							, @vl_subtotal
							, @vl_porc_dscto1
							, @vl_monto_dscto1
							, @vl_porc_dscto2
							, @vl_monto_dscto2
							, @vl_total_neto
							, @vl_porc_iva
							, @vl_monto_iva
							, @vl_total_con_iva
							, getdate()
							, @vl_cod_forma_pago
							, getdate()
							, @vl_motivo_anula
							, @vl_cod_usuario_anula
							, @vl_porc_dscto_corporativo)
			SET IDENTITY_INSERT NOTA_VENTA OFF
			SELECT *
			FROM NOTA_VENTA
			WHERE cod_nota_venta = @ve_cod_nv
		END
	ELSE
		PRINT 'EXISTE RESGISTRO'
END
go