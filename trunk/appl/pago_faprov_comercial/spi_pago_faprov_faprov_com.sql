CREATE PROCEDURE spi_pago_faprov_faprov_com(@ve_cod_pago_faprov_faprov_com	numeric
										   ,@ve_cod_pago_faprov				numeric
										   ,@ve_cod_faprov					numeric
										   ,@ve_monto_asignado				numeric
										   ,@ve_cod_usuario_ws				numeric
										   ,@ve_nro_faprov					numeric
										   ,@ve_fecha_faprov				datetime
										   ,@ve_total_con_iva_fa			numeric
										   ,@ve_monto_ncprov				numeric
										   ,@ve_saldo_sin_pago_faprov		numeric
										   ,@ve_pago_anterior				numeric
										   ,@ve_nom_cuenta_corriente		varchar(50))

AS
BEGIN		
	insert into pago_faprov_faprov_com
				(cod_pago_faprov_faprov_com
				,cod_pago_faprov
				,cod_faprov
				,monto_asignado
				,cod_usuario_ws
				,nro_faprov
				,fecha_faprov
				,total_con_iva_fa
				,monto_nc_prov
				,saldo_sin_pago_faprov
				,pago_anterior
				,nom_cuenta_corriente)
		  values 
				(@ve_cod_pago_faprov_faprov_com
				,@ve_cod_pago_faprov
				,@ve_cod_faprov
				,@ve_monto_asignado
				,@ve_cod_usuario_ws
				,@ve_nro_faprov
				,@ve_fecha_faprov
				,@ve_total_con_iva_fa
				,@ve_monto_ncprov
				,@ve_saldo_sin_pago_faprov
				,@ve_pago_anterior
				,@ve_nom_cuenta_corriente)
END				