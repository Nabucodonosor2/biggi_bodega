-------------------- spu_ingreso_pago_factura ---------------------------------
CREATE PROCEDURE [dbo].[spu_ingreso_pago_factura]
			(@ve_operacion					varchar(20)
			,@ve_cod_ingreso_pago_factura	numeric
			,@ve_cod_ingreso_pago			numeric
			,@ve_cod_doc					numeric  = null
			,@ve_tipo_doc					varchar(30) = null
			,@ve_monto_asignado				T_PRECIO = null)

AS
BEGIN
		if (@ve_operacion='UPDATE') 
			begin
				UPDATE ingreso_pago_factura		
				SET		cod_ingreso_pago	= @ve_cod_ingreso_pago
					   ,monto_asignado		= @ve_monto_asignado
					   ,tipo_doc			= @ve_tipo_doc
					   ,cod_doc			    = @ve_cod_doc
				WHERE cod_ingreso_pago_factura = @ve_cod_ingreso_pago_factura 
						
			end
		else if (@ve_operacion='INSERT') 
			begin
				insert into ingreso_pago_factura
					(cod_ingreso_pago
					,monto_asignado
					,tipo_doc
					,cod_doc)
				values 
					(@ve_cod_ingreso_pago
					,@ve_monto_asignado
					,@ve_tipo_doc
					,@ve_cod_doc)
			end	
END
go