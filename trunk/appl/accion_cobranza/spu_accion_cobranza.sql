------------------  spu_accion_cobranza  ----------------------------
CREATE PROCEDURE [dbo].[spu_accion_cobranza](@ve_operacion varchar(20), @ve_cod_accion_cobranza numeric,@ve_nom_accion_cobranza varchar(100)=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into accion_cobranza (nom_accion_cobranza)
		values (@ve_nom_accion_cobranza)
	end 
	if (@ve_operacion='UPDATE') begin
		update	accion_cobranza
		set		nom_accion_cobranza	= @ve_nom_accion_cobranza
		where	cod_accion_cobranza	= @ve_cod_accion_cobranza
	end
	else if (@ve_operacion='DELETE') begin
		delete accion_cobranza 
    	where cod_accion_cobranza = @ve_cod_accion_cobranza
	end
END
go