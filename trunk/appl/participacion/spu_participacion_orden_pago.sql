alter PROCEDURE [dbo].[spu_participacion_orden_pago]
			(@ve_operacion					varchar(20)
			,@ve_cod_participacion				numeric
			,@ve_cod_orden_pago					numeric  = null
			,@ve_monto_asignado					numeric  = null)

AS
BEGIN
		if (@ve_operacion='INSERT') 
			insert into participacion_orden_pago
				(cod_participacion
				,cod_orden_pago
				,monto_asignado)
			values 
				(@ve_cod_participacion
				,@ve_cod_orden_pago
				,@ve_monto_asignado)
		else if (@ve_operacion='DELETE_ALL') 
			delete participacion_orden_pago
			where cod_participacion = @ve_cod_participacion 
END
go