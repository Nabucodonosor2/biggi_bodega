CREATE PROCEDURE spi_pago_faprov_comercial(@ve_cod_pago_faprov				numeric
										  ,@ve_fecha_pago_faprov			datetime
										  ,@ve_nom_usuario					varchar(100)
										  ,@ve_nro_documento				numeric
										  ,@ve_fecha_documento				datetime
										  ,@ve_monto_documento				T_PRECIO
										  ,@ve_cod_usuario_ws				numeric
										  ,@ve_cod_empresa					numeric
										  ,@ve_nom_empresa					varchar(100)
										  ,@ve_rut							varchar(50)
										  ,@ve_alias						varchar(50)
										  ,@ve_nom_tipo_pago_faprov			varchar(50))

AS
BEGIN		
	insert into pago_faprov_comercial
				(cod_pago_faprov_comercial
				,fecha_pago_faprov
				,nom_usuario
				,nro_documento
				,fecha_documento
				,monto_documento
				,cod_usuario_ws
				,cod_empresa
				,nom_empresa
				,rut
				,alias
				,nom_tipo_pago_faprov)
		  values 
				(@ve_cod_pago_faprov
				,@ve_fecha_pago_faprov
				,@ve_nom_usuario
				,@ve_nro_documento
				,@ve_fecha_documento
				,@ve_monto_documento
				,@ve_cod_usuario_ws
				,@ve_cod_empresa
				,@ve_nom_empresa
				,@ve_rut
				,@ve_alias
				,@ve_nom_tipo_pago_faprov)
END