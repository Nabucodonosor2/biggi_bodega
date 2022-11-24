-------------------- spu_moneda ---------------------------------
CREATE PROCEDURE [dbo].[spu_moneda](@ve_operacion varchar(20),@ve_cod_moneda numeric, @ve_nom_moneda varchar(100)=NULL, @ve_simbolo varchar(10)=NULL, @ve_orden numeric=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into moneda (nom_moneda, simbolo, orden)
		values (@ve_nom_moneda, @ve_simbolo, @ve_orden)
	end 
	if (@ve_operacion='UPDATE') begin
		update moneda 
		set nom_moneda = @ve_nom_moneda, simbolo = @ve_simbolo, orden = @ve_orden
	    where cod_moneda = @ve_cod_moneda
	end
	else if (@ve_operacion='DELETE') begin
		delete moneda 
    	where cod_moneda = @ve_cod_moneda
	end
	
	EXECUTE sp_orden_parametricas 'MONEDA'
END
go