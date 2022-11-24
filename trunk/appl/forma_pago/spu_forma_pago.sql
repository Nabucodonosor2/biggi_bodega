-------------------- spu_forma_pago ---------------------------------
CREATE PROCEDURE [dbo].[spu_forma_pago](@ve_operacion varchar(20), @ve_cod_forma_pago numeric, @ve_nom_forma_pago varchar(100)=NULL, @ve_orden numeric=NULL, @ve_cantidad_doc numeric=NULL)
AS
BEGIN
if (@ve_operacion='INSERT') begin
		insert into forma_pago (nom_forma_pago,orden,cantidad_doc)
		values (@ve_nom_forma_pago,@ve_orden,@ve_cantidad_doc)
	end 
	if (@ve_operacion='UPDATE') begin
		update forma_pago 
		set nom_forma_pago = @ve_nom_forma_pago, 
			orden = @ve_orden,
			cantidad_doc = @ve_cantidad_doc
	    where cod_forma_pago = @ve_cod_forma_pago
	end
	else if (@ve_operacion='DELETE') begin
		delete forma_pago 
    	where cod_forma_pago = @ve_cod_forma_pago
	end	

	EXECUTE sp_orden_parametricas 'FORMA_PAGO'
END
go