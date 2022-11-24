--------------------------------spx_renombra_orden_compra--------------------------------
/*este sp permite renombrar una OC
- @ve_cod_orden_compra_actual: el codigo de OC a modificar, luego esta OC la elimina 
- @ve_cod_orden_compra_nueva: el nuevo codigo de OC
*/
ALTER PROCEDURE [dbo].[spx_renombra_orden_compra] (@ve_cod_orden_compra_actual numeric, @ve_cod_orden_compra_nueva numeric)		
AS
BEGIN
	SET IDENTITY_INSERT orden_compra ON
	--inserta OC
	INSERT INTO ORDEN_COMPRA
           ([COD_ORDEN_COMPRA]
           ,[FECHA_ORDEN_COMPRA]
           ,[COD_USUARIO]
           ,[COD_USUARIO_SOLICITA]
           ,[COD_MONEDA]
           ,[COD_ESTADO_ORDEN_COMPRA]
           ,[COD_NOTA_VENTA]
           ,[COD_CUENTA_CORRIENTE]
           ,[REFERENCIA]
           ,[COD_EMPRESA]
           ,[COD_SUCURSAL]
           ,[COD_PERSONA]
           ,[SUBTOTAL]
           ,[PORC_DSCTO1]
           ,[MONTO_DSCTO1]
           ,[INGRESO_USUARIO_DSCTO1]
           ,[PORC_DSCTO2]
           ,[MONTO_DSCTO2]
           ,[INGRESO_USUARIO_DSCTO2]
           ,[TOTAL_NETO]
           ,[PORC_IVA]
           ,[MONTO_IVA]
           ,[TOTAL_CON_IVA]
           ,[OBS]
           ,[FECHA_ANULA]
           ,[MOTIVO_ANULA]
           ,[COD_USUARIO_ANULA]
		   ,[TIPO_ORDEN_COMPRA])
	(SELECT @ve_cod_orden_compra_nueva --COD_ORDEN_COMPRA
           ,FECHA_ORDEN_COMPRA
           ,COD_USUARIO
           ,COD_USUARIO_SOLICITA
           ,COD_MONEDA
           ,COD_ESTADO_ORDEN_COMPRA
           ,COD_NOTA_VENTA
           ,COD_CUENTA_CORRIENTE
           ,REFERENCIA
           ,COD_EMPRESA
           ,COD_SUCURSAL
           ,COD_PERSONA
           ,SUBTOTAL
           ,PORC_DSCTO1
           ,MONTO_DSCTO1
           ,INGRESO_USUARIO_DSCTO1
           ,PORC_DSCTO2
           ,MONTO_DSCTO2
           ,INGRESO_USUARIO_DSCTO2
           ,TOTAL_NETO
           ,PORC_IVA
           ,MONTO_IVA
           ,TOTAL_CON_IVA
           ,OBS
           ,FECHA_ANULA
           ,MOTIVO_ANULA
           ,COD_USUARIO_ANULA
		   ,TIPO_ORDEN_COMPRA 
			FROM ORDEN_COMPRA
			WHERE COD_ORDEN_COMPRA = @ve_cod_orden_compra_actual)
	SET IDENTITY_INSERT orden_compra OFF

	--inserta item_oc
	INSERT INTO ITEM_ORDEN_COMPRA
           ([COD_ORDEN_COMPRA]
           ,[ORDEN]
           ,[ITEM]
           ,[COD_PRODUCTO]
           ,[NOM_PRODUCTO]
           ,[CANTIDAD]
           ,[PRECIO]
           ,[COD_ITEM_NOTA_VENTA]
           ,[COD_TIPO_TE]
           ,[MOTIVO_TE])
	(select @ve_cod_orden_compra_nueva --COD_ORDEN_COMPRA
           ,ORDEN
           ,ITEM
           ,COD_PRODUCTO
           ,NOM_PRODUCTO
           ,CANTIDAD
           ,PRECIO
           ,COD_ITEM_NOTA_VENTA
           ,COD_TIPO_TE
           ,MOTIVO_TE 
			FROM ITEM_ORDEN_COMPRA 
			WHERE COD_ORDEN_COMPRA = @ve_cod_orden_compra_actual)

	DELETE ITEM_ORDEN_COMPRA
	WHERE COD_ORDEN_COMPRA = @ve_cod_orden_compra_actual 

	DELETE ORDEN_COMPRA
	WHERE COD_ORDEN_COMPRA = @ve_cod_orden_compra_actual 
	

END
go